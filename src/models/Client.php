<?php
/**
 * Model Client
 * Web Aguaboa - Gestão Comercial
 */

class Client {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os clientes
     */
    public function findAll($search = '', $sortBy = '', $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM clients WHERE 1=1";
        $params = [];
        
        // Aplicar busca
        if (!empty($search)) {
            $sql .= " AND (cliente LIKE ? OR empresa LIKE ? OR cidade LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Aplicar ordenação
        switch ($sortBy) {
            case 'premium_only':
                $sql .= " AND cliente_premium = 1";
                break;
            case 'exclusivo_only':
                $sql .= " AND cliente_exclusivo = 1";
                break;
        }
        
        // Ordenação padrão
        if ($sortBy !== 'envase_desc') {
            $sql .= " ORDER BY cliente ASC";
        }
        
        // Paginação
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar clientes com estatísticas de envase
     */
    public function findAllWithEnvaseStats($search = '', $sortBy = '', $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];
        
        if ($search) {
            $where = "WHERE (c.cliente LIKE ? OR c.empresa LIKE ? OR c.cidade LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $orderBy = 'c.cliente';
        switch ($sortBy) {
            case 'empresa':
                $orderBy = 'c.empresa';
                break;
            case 'cidade':
                $orderBy = 'c.cidade';
                break;
            case 'total_envases':
                $orderBy = 'total_envases DESC';
                break;
            case 'ultimo_envase':
                $orderBy = 'ultimo_envase DESC';
                break;
        }
        
        $sql = "SELECT c.*, 
                       COALESCE(e.total_registros, 0) as total_registros_envase,
                       COALESCE(e.total_quantidade, 0) as total_envases,
                       COALESCE(e.produtos_diferentes, 0) as produtos_diferentes,
                       e.ultimo_envase,
                       e.produto_principal
                FROM clients c
                LEFT JOIN (
                    SELECT empresa,
                           COUNT(*) as total_registros,
                           SUM(quantidade) as total_quantidade,
                           COUNT(DISTINCT produto) as produtos_diferentes,
                           MAX(CONCAT(ano, '-', LPAD(mes, 2, '0'), '-', LPAD(dia, 2, '0'))) as ultimo_envase,
                           (
                               SELECT produto 
                               FROM envase_data e2 
                               WHERE e2.empresa = e1.empresa 
                               GROUP BY produto 
                               ORDER BY SUM(quantidade) DESC 
                               LIMIT 1
                           ) as produto_principal
                    FROM envase_data e1
                    GROUP BY empresa
                ) e ON (c.cliente = e.empresa OR c.empresa = e.empresa)
                $where
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Contar total de clientes
     */
    public function countAll($search = '', $sortBy = '') {
        $sql = "SELECT COUNT(*) FROM clients WHERE 1=1";
        $params = [];
        
        // Aplicar busca
        if (!empty($search)) {
            $sql .= " AND (cliente LIKE ? OR empresa LIKE ? OR cidade LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Aplicar filtros
        switch ($sortBy) {
            case 'premium_only':
                $sql .= " AND cliente_premium = 1";
                break;
            case 'exclusivo_only':
                $sql .= " AND cliente_exclusivo = 1";
                break;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Buscar cliente por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM clients WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Criar cliente de envase automaticamente
     */
    public function createFromEnvase($nomeEmpresa, $cidade = null) {
        $sql = "INSERT INTO clients (cliente, empresa, cidade, tipo_cliente, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $nomeEmpresa,
            $nomeEmpresa,
            $cidade,
            'envase'
        ]);
    }
    
    /**
     * Buscar cliente por nome da empresa
     */
    public function findByName($nomeEmpresa) {
        $sql = "SELECT * FROM clients WHERE cliente = ? OR empresa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nomeEmpresa, $nomeEmpresa]);
        
        return $stmt->fetch();
    }
    
    /**
     * Criar novo cliente
     */
    public function create($data) {
        $sql = "INSERT INTO clients (cliente, empresa, cidade, estado, tipo_cliente, cliente_exclusivo, cliente_premium, tipo_frete, freteiro_nome) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['cliente'],
            $data['empresa'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['tipo_cliente'] ?? null,
            $data['cliente_exclusivo'] ? 1 : 0,
            $data['cliente_premium'] ? 1 : 0,
            $data['tipo_frete'] ?? null,
            $data['freteiro_nome'] ?? null
        ]);
    }
    
    /**
     * Atualizar cliente
     */
    public function update($id, $data) {
        $sql = "UPDATE clients SET 
                cliente = ?, empresa = ?, cidade = ?, estado = ?, tipo_cliente = ?, 
                cliente_exclusivo = ?, cliente_premium = ?, tipo_frete = ?, freteiro_nome = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['cliente'],
            $data['empresa'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['tipo_cliente'] ?? null,
            $data['cliente_exclusivo'] ? 1 : 0,
            $data['cliente_premium'] ? 1 : 0,
            $data['tipo_frete'] ?? null,
            $data['freteiro_nome'] ?? null,
            $id
        ]);
    }
    
    /**
     * Deletar cliente
     */
    public function delete($id) {
        // Primeiro, deletar ações relacionadas
        $this->db->prepare("DELETE FROM actions WHERE client_id = ?")->execute([$id]);
        // client_infos removido - não existe mais no banco
        
        // Depois, deletar o cliente
        $sql = "DELETE FROM clients WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Buscar ações do cliente
     */
    public function getActions($clientId) {
        $sql = "SELECT * FROM actions WHERE client_id = ? ORDER BY data_acao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar informações adicionais do cliente
     */
    public function getInfos($clientId) {
        // client_infos removido - retornando array vazio
        return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Estatísticas dos clientes
     */
    public function getStats() {
        $stats = [];
        
        // Total de clientes
        $stmt = $this->db->query("SELECT COUNT(*) FROM clients");
        $stats['total'] = $stmt->fetchColumn();
        
        // Clientes exclusivos
        $stmt = $this->db->query("SELECT COUNT(*) FROM clients WHERE cliente_exclusivo = 1");
        $stats['exclusivos'] = $stmt->fetchColumn();
        
        // Clientes premium
        $stmt = $this->db->query("SELECT COUNT(*) FROM clients WHERE cliente_premium = 1");
        $stats['premium'] = $stmt->fetchColumn();
        
        // Por tipo de cliente
        $stmt = $this->db->query("SELECT tipo_cliente, COUNT(*) as count FROM clients WHERE tipo_cliente IS NOT NULL GROUP BY tipo_cliente");
        $stats['por_tipo'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Buscar cliente por empresa (para integração com envase)
     */
    public function findByEmpresa($empresa) {
        $sql = "SELECT * FROM clients WHERE LOWER(empresa) = LOWER(?) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresa]);
        
        return $stmt->fetch();
    }
    
    /**
     * Verificar se nome do cliente já existe
     */
    public function clienteExists($cliente, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM clients WHERE cliente = ?";
        $params = [$cliente];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Encontrar clientes duplicados/similares
     */
    public function findDuplicateClients() {
        // Buscar todos os clientes primeiro
        $sql = "SELECT c.*, COALESCE(e.total_envases, 0) as total_envases
                FROM clients c
                LEFT JOIN (
                    SELECT empresa, SUM(quantidade) as total_envases
                    FROM envase_data
                    GROUP BY empresa
                ) e ON (c.cliente = e.empresa OR c.empresa = e.empresa)
                ORDER BY c.cliente";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $allClients = $stmt->fetchAll();
        
        $groups = [];
        $processed = [];
        
        // Comparar cada cliente com todos os outros
        foreach ($allClients as $client1) {
            if (in_array($client1['id'], $processed)) {
                continue;
            }
            
            $group = [$client1];
            $processed[] = $client1['id'];
            
            foreach ($allClients as $client2) {
                if ($client1['id'] >= $client2['id'] || in_array($client2['id'], $processed)) {
                    continue;
                }
                
                if ($this->clientsAreSimilar($client1, $client2)) {
                    $group[] = $client2;
                    $processed[] = $client2['id'];
                }
            }
            
            // Só adicionar grupos com mais de 1 cliente
            if (count($group) > 1) {
                // Ordenar por maior volume de envase (cliente principal primeiro)
                usort($group, function($a, $b) {
                    return $b['total_envases'] <=> $a['total_envases'];
                });
                $groups[] = $group;
            }
        }
        
        return $groups;
    }
    
    /**
     * Verificar se dois clientes são similares
     */
    private function clientsAreSimilar($client1, $client2) {
        $name1 = strtolower(trim($client1['cliente']));
        $name2 = strtolower(trim($client2['cliente']));
        
        // 1. Nomes idênticos
        if ($name1 === $name2) {
            return true;
        }
        
        // 2. Limpar nomes para comparação básica
        $clean1 = $this->cleanClientName($name1);
        $clean2 = $this->cleanClientName($name2);
        
        if ($clean1 === $clean2) {
            return true;
        }
        
        // 3. Padrão EMBU DISTR - casos específicos
        if (strpos($name1, 'embu distr') !== false && strpos($name2, 'embu distr') !== false) {
            return true; // Todos os EMBU DISTR são considerados similares
        }
        
        // 4. Padrão ENTREPOSTO - casos específicos  
        if (strpos($name1, 'entreposto') !== false && strpos($name2, 'entreposto') !== false) {
            return true; // Todos os ENTREPOSTO são considerados similares
        }
        
        // 5. Extrair base do nome removendo parênteses e conteúdo
        $base1 = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name1);
        $base2 = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name2);
        $base1 = trim(preg_replace('/\s+/', ' ', $base1));
        $base2 = trim(preg_replace('/\s+/', ' ', $base2));
        
        if (strlen($base1) > 5 && strlen($base2) > 5 && $base1 === $base2) {
            return true;
        }
        
        // 6. Verificar se empresas são idênticas
        if (!empty($client1['empresa']) && !empty($client2['empresa'])) {
            $empresa1 = strtolower(trim($client1['empresa']));
            $empresa2 = strtolower(trim($client2['empresa']));
            if ($empresa1 === $empresa2) {
                return true;
            }
        }
        
        // 7. Comparar base do nome removendo números e variações do final
        $base1_clean = preg_replace('/\s*[0-9]+\s*$/', '', $clean1);
        $base2_clean = preg_replace('/\s*[0-9]+\s*$/', '', $clean2);
        
        if (strlen($base1_clean) > 5 && strlen($base2_clean) > 5 && $base1_clean === $base2_clean) {
            return true;
        }
        
        // 8. Verificar prefixos comuns longos
        $words1 = explode(' ', $clean1);
        $words2 = explode(' ', $clean2);
        
        if (count($words1) >= 2 && count($words2) >= 2) {
            // Se primeiras 2-3 palavras são iguais e nomes longos
            $prefix1 = implode(' ', array_slice($words1, 0, min(3, count($words1))));
            $prefix2 = implode(' ', array_slice($words2, 0, min(3, count($words2))));
            
            if (strlen($prefix1) > 8 && $prefix1 === $prefix2) {
                return true;
            }
        }
        
        // 9. Similaridade por Levenshtein (para casos muito próximos)
        if (strlen($clean1) > 10 && strlen($clean2) > 10) {
            $distance = levenshtein($clean1, $clean2);
            $maxLen = max(strlen($clean1), strlen($clean2));
            $similarity = 1 - ($distance / $maxLen);
            
            // Se similaridade for maior que 85% 
            if ($similarity > 0.85 && $distance <= 3) {
                return true;
            }
        }
        
        // 10. Casos especiais com variações comuns (LTDA, S/A, etc.)
        $variations1 = $this->removeCommonVariations($clean1);
        $variations2 = $this->removeCommonVariations($clean2);
        
        if ($variations1 === $variations2 && strlen($variations1) > 6) {
            return true;
        }
        
        // 11. Verificar se um nome contém o outro (para casos como "AGUA PURA" e "AGUA PURA TATUI")
        if (strlen($clean1) > 8 && strlen($clean2) > 8) {
            if (strpos($clean1, $clean2) !== false || strpos($clean2, $clean1) !== false) {
                $shorter = strlen($clean1) < strlen($clean2) ? $clean1 : $clean2;
                $longer = strlen($clean1) > strlen($clean2) ? $clean1 : $clean2;
                
                // Se o nome menor tem pelo menos 8 caracteres e está contido no maior
                if (strlen($shorter) >= 8 && strpos($longer, $shorter) === 0) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Remover variações comuns de nomes de empresa
     */
    private function removeCommonVariations($name) {
        $variations = [
            ' ltda', ' sa', ' eireli', ' me', ' epp', ' cia',
            ' comercial', ' distribuidora', ' distr', ' comercio', 
            ' emp', ' empresa', 's/a'
        ];
        
        $name = ' ' . trim(strtolower($name)) . ' ';
        
        foreach ($variations as $variation) {
            $name = str_replace($variation, ' ', $name);
        }
        
        // Remover espaços extras
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);
        
        return $name;
    }
    
    /**
     * Limpar nome do cliente para comparação
     */
    private function cleanClientName($name) {
        // Converter para minúsculas primeiro
        $name = mb_strtolower($name, 'UTF-8');
        
        // Remover acentos usando método mais seguro
        $unwanted_array = [
            'S'=>'S', 's'=>'s', 'Z'=>'Z', 'z'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        $name = strtr($name, $unwanted_array);
        
        // Remover caracteres especiais, manter apenas letras, números e espaços
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        
        // Remover espaços extras
        $name = preg_replace('/\s+/', ' ', trim($name));
        
        return $name;
    }
    
    /**
     * Unificar dados de envase de um cliente para outro
     */
    public function mergeEnvaseData($sourceClientId, $targetClientId) {
        $sourceClient = $this->findById($sourceClientId);
        $targetClient = $this->findById($targetClientId);
        
        if (!$sourceClient || !$targetClient) {
            return false;
        }
        
        // Atualizar registros de envase_data
        $sql = "UPDATE envase_data SET empresa = ? 
                WHERE empresa = ? OR empresa = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $targetClient['cliente'],
            $sourceClient['cliente'],
            $sourceClient['empresa']
        ]);
    }
    
    /**
     * Unificar ações de um cliente para outro
     */
    public function mergeActions($sourceClientId, $targetClientId) {
        $sql = "UPDATE actions SET client_id = ? WHERE client_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$targetClientId, $sourceClientId]);
    }
    
    /**
     * Unificar todos os clientes duplicados automaticamente
     */
    public function unifyAllDuplicates() {
        $duplicateGroups = $this->findDuplicateClients();
        $unifiedCount = 0;
        $errors = [];
        
        foreach ($duplicateGroups as $group) {
            try {
                // O primeiro cliente do grupo é o master (maior volume)
                $masterClient = $group[0];
                $masterClientId = $masterClient['id'];
                
                // Unificar todos os outros clientes do grupo
                for ($i = 1; $i < count($group); $i++) {
                    $clientToMerge = $group[$i];
                    $clientId = $clientToMerge['id'];
                    
                    // Unificar dados de envase
                    $this->mergeEnvaseData($clientId, $masterClientId);
                    
                    // Unificar ações
                    $this->mergeActions($clientId, $masterClientId);
                    
                    // Deletar cliente duplicado
                    if ($this->delete($clientId)) {
                        $unifiedCount++;
                    } else {
                        $errors[] = "Erro ao deletar cliente: {$clientToMerge['cliente']}";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Erro ao processar grupo: " . $e->getMessage();
            }
        }
        
        return [
            'unified_count' => $unifiedCount,
            'errors' => $errors,
            'groups_processed' => count($duplicateGroups)
        ];
    }
    
    /**
     * Buscar todos os clientes por nome base (para debug)
     */
    public function findClientsByBaseName($baseName) {
        $cleanBaseName = $this->cleanClientName($baseName);
        
        $sql = "SELECT * FROM clients WHERE 1=1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $allClients = $stmt->fetchAll();
        
        $matches = [];
        foreach ($allClients as $client) {
            $cleanClientName = $this->cleanClientName($client['cliente']);
            $baseClientName = preg_replace('/[\s]*[0-9]+[\s]*$/', '', $cleanClientName);
            
            if ($baseClientName === $cleanBaseName || $cleanClientName === $cleanBaseName) {
                $matches[] = $client;
            }
        }
        
        return $matches;
    }
}