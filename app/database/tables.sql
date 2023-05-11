DROP TABLE usuarios;

CREATE TABLE IF NOT EXISTS usuarios (
    id              INTEGER PRIMARY KEY,
    nome            TEXT    NOT NULL,
    dataNascimento  TEXT,
    tipo            INTEGER,
    ativado         INTEGER
);

INSERT INTO usuarios (id, nome, dataNascimento, tipo, ativado) values (1,'teste','01-01-2000',1,1);

DROP TABLE veiculos;

CREATE TABLE IF NOT EXISTS veiculos (
    id              INTEGER PRIMARY KEY,
    placa           TEXT    NOT NULL,
    modelo          TEXT,
    cor             TEXT,
    ano         INTEGER
);