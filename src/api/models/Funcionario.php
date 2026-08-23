<?php

namespace Api\Models; // Ajuste conforme sua estrutura de pastas

use Api\Models\Cargo;
use JsonSerializable;

class Funcionario implements JsonSerializable
{
    // Atributos privados
    private int $idFuncionario;
    private Cargo $cargo;
    private string $nomeFuncionario;
    private string $email;
    private string $senha;
    private int $recebeValeTransporte;

    public function __construct()
    {
        // error_log("⬆️  Funcionario::__construct()\n");
        $this->cargo = new Cargo();
    }

    // Getter e Setter para idFuncionario
    public function getIdFuncionario(): int
    {
        return $this->idFuncionario;
    }

    public function setIdFuncionario($valor): void
    {
        if (!is_numeric($valor) || intval($valor) != $valor) {
            throw new \Exception("idFuncionario deve ser um número inteiro.");
        }
        if ($valor <= 0) {
            throw new \Exception("idFuncionario deve ser um número inteiro positivo.");
        }
        $this->idFuncionario = intval($valor);
    }

    // Getter e Setter para cargo
    public function getCargo(): Cargo
    {
        return $this->cargo;
    }

    public function setCargo($cargo): void
    {
        if (!($cargo instanceof Cargo)) {
            throw new \Exception("cargo deve ser uma instância válida de Cargo.");
        }
        $this->cargo = $cargo;
    }

    // Getter e Setter para nomeFuncionario
    public function getNomeFuncionario(): string
    {
        return $this->nomeFuncionario;
    }

    public function setNomeFuncionario(string $nome): void
    {
        $nome = trim($nome);
        if (strlen($nome) < 3) {
            throw new \Exception("nomeFuncionario deve ter pelo menos 3 caracteres.");
        }
        $this->nomeFuncionario = $nome;
    }

    // Getter e Setter para email
    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("email em formato inválido.");
        }
        $this->email = $email;
    }

    // Getter e Setter para senha
    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): void
    {
        $senha = trim($senha);
        if (strlen($senha) < 6) {
            throw new \Exception("senha deve ter pelo menos 6 caracteres.");
        }
        if (!preg_match("/[A-Z]/", $senha)) {
            throw new \Exception("senha deve conter pelo menos uma letra maiúscula.");
        }
        if (!preg_match("/[0-9]/", $senha)) {
            throw new \Exception("senha deve conter pelo menos um número.");
        }
        if (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $senha)) {
            throw new \Exception("senha deve conter pelo menos um caractere especial.");
        }
        $this->senha = $senha;
    }

    // Getter e Setter para recebeValeTransporte
    public function getRecebeValeTransporte(): int
    {
        return $this->recebeValeTransporte;
    }

    public function setRecebeValeTransporte(int $valor): void
    {
        if ($valor !== 0 && $valor !== 1) {
            throw new \Exception("recebeValeTransporte deve ser 0 ou 1.");
        }
        $this->recebeValeTransporte = $valor;
    }

    /**
     * Implementação da interface JsonSerializable
     * 
     * Permite que objetos da classe sejam convertidos corretamente
     * para JSON ao usar json_encode(), exibindo apenas dados relevantes.
     */
    public function jsonSerialize(): array
    {
        return [
            'idFuncionario' => $this->getIdFuncionario(),
            'cargo' => $this->getCargo() ? $this->getCargo() : null,
            'nomeFuncionario' => $this->getNomeFuncionario(),
            'email' => $this->getEmail(),
            'recebeValeTransporte' => $this->getRecebeValeTransporte()
            // ⚠️ senha não deve ser exposta em respostas JSON por segurança
        ];
    }
}
