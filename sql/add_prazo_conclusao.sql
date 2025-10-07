-- Adicionar coluna prazo_conclusao na tabela actions
ALTER TABLE actions ADD COLUMN prazo_conclusao DATE AFTER data_acao;

-- Verificar a estrutura atualizada
DESCRIBE actions;