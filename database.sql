-- Criação do banco e tabelas principais
DROP DATABASE IF EXISTS vault_db;
CREATE DATABASE vault_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vault_db;

-- Usuários (1 usuário didático)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(100) NOT NULL
);

INSERT INTO usuarios (nome, email, senha) VALUES ('Usuário Demo', 'demo@vault.test', '123456');

-- Categorias de logins
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    cor_hex CHAR(7) NOT NULL
);

INSERT INTO categorias (nome, cor_hex) VALUES
('Social', '#1da1f2'),
('Bancos', '#bb86fc'),
('Trabalho', '#00c853');

-- Bancos (nova entidade)
CREATE TABLE bancos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    codigo_banco VARCHAR(20) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO bancos (nome, codigo_banco) VALUES
('Nubank', '260'),
('Itaú', '341'),
('Bradesco', '237');

-- Logins (senhas em texto puro para visualização didática)
CREATE TABLE logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    site_nome VARCHAR(120) NOT NULL,
    site_url VARCHAR(200),
    login VARCHAR(120) NOT NULL,
    senha VARCHAR(120) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_logins_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_logins_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT
);

-- Cartões
CREATE TABLE cartoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_banco INT NOT NULL,
    titular VARCHAR(120) NOT NULL,
    numero VARCHAR(30) NOT NULL,
    validade CHAR(7) NOT NULL,
    cvv CHAR(4) NOT NULL,
    bandeira VARCHAR(40) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_cartoes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_cartoes_banco FOREIGN KEY (id_banco) REFERENCES bancos(id) ON DELETE RESTRICT
);

-- Dados de exemplo
INSERT INTO logins (id_usuario, id_categoria, site_nome, site_url, login, senha) VALUES
(1, 1, 'Twitter', 'https://twitter.com', 'demo_user', 'senha123'),
(1, 3, 'Email Corporativo', 'https://mail.empresa.com', 'usuario@empresa.com', 'SenhaForte!');

INSERT INTO cartoes (id_usuario, id_banco, titular, numero, validade, cvv, bandeira) VALUES
(1, 1, 'Usuário Demo', '5555 4444 3333 1111', '12/28', '123', 'Mastercard');

-- Logs (auditoria simples)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    acao VARCHAR(80) NOT NULL,
    detalhes VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

INSERT INTO logs (id_usuario, acao, detalhes) VALUES
(1, 'LOGIN_SUCESSO', 'Usuário Demo acessou o painel'),
(1, 'BANCO_CRIADO', 'Banco Itaú cadastrado'),
(1, 'CARTAO_ADICIONADO', 'Cartão Mastercard vinculado ao banco Nubank');
