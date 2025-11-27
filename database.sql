-- Banco e tabelas para Gerenciador de Senhas e Carteira Digital
CREATE DATABASE IF NOT EXISTS vault_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vault_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE
);

INSERT INTO usuarios (nome, email) VALUES
('Usuário Demo', 'demo@vault.com')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), email = VALUES(email);

-- Categorias para logins
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(60) NOT NULL,
  cor_hex CHAR(7) NOT NULL
);

INSERT INTO categorias (id, nome, cor_hex) VALUES
(1, 'Social', '#1da1f2'),
(2, 'Bancos', '#bb86fc'),
(3, 'Trabalho', '#00c853')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), cor_hex = VALUES(cor_hex);

-- Tabela de logins (possui FKs para usuarios e categorias)
CREATE TABLE IF NOT EXISTS logins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_categoria INT NOT NULL,
  site_nome VARCHAR(120) NOT NULL,
  site_url VARCHAR(200) DEFAULT NULL,
  login VARCHAR(150) NOT NULL,
  senha VARCHAR(150) NOT NULL,
  -- Chave estrangeira para usuarios
  CONSTRAINT fk_logins_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  -- Chave estrangeira para categorias
  CONSTRAINT fk_logins_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT
);

-- Tabela de cartões (FK para usuarios)
CREATE TABLE IF NOT EXISTS cartoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  titular VARCHAR(120) NOT NULL,
  numero VARCHAR(30) NOT NULL,
  validade CHAR(7) NOT NULL,
  cvv CHAR(4) NOT NULL,
  bandeira VARCHAR(30) NOT NULL,
  -- Chave estrangeira para usuarios
  CONSTRAINT fk_cartoes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);
