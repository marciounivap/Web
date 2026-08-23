<?php
namespace Api\Models;
use InvalidArgumentException;
use \JsonSerializable;

/**
 * Representa a entidade Cargo do sistema.
 *
 * Objetivo:
 * - Encapsular os dados de um cargo.
 * - Garantir integridade dos atributos via getters e setters.
 */
class Cargo implements JsonSerializable
{
    /** @var int Identificador único do cargo */
    private int $idCargo;

    /** @var string|null Nome do cargo */
    private string $nomeCargo = "";

    public function __construct()
    {
        // error_log("⬆️  Cargo::__construct()\n");
    }

    /**
     * Getter para idCargo
     * @return int|null Identificador único do cargo
     */
    public function getIdCargo(): ?int
    {
        return $this->idCargo;
    }

    /**
     * Define o ID do cargo.
     *
     * 🔹 Regra de domínio: garante que o ID seja sempre um número inteiro positivo.
     *
     * @param int $value Número inteiro positivo representando o ID do cargo.
     * @throws InvalidArgumentException se o valor for inválido.
     */
    public function setIdCargo(int $value): void
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException("idCargo deve ser um número inteiro.");
        }

        if ($value <= 0) {
            throw new InvalidArgumentException("idCargo deve ser maior que zero.");
        }

        $this->idCargo = $value;
    }

    /**
     * Getter para nomeCargo
     * @return string|null Nome do cargo
     */
    public function getNomeCargo(): ?string
    {
        return $this->nomeCargo;
    }

    /**
     * Define o nome do cargo.
     *
     * 🔹 Regra de domínio: garante que o nome seja sempre uma string não vazia
     * e com pelo menos 3 caracteres e no máximo 64.
     *
     * @param string $value Nome do cargo.
     * @throws InvalidArgumentException se o valor for inválido.
     */
    public function setNomeCargo(string $value): void
    {
        $nome = trim($value);

        if ($nome === '') {
            throw new InvalidArgumentException("nomeCargo não pode ser vazio.");
        }

        $len = mb_strlen($nome);

        if ($len < 3) {
            throw new InvalidArgumentException("nomeCargo deve ter pelo menos 3 caracteres.");
        }

        if ($len > 64) {
            throw new InvalidArgumentException("nomeCargo deve ter no máximo 64 caracteres.");
        }

        $this->nomeCargo = $nome;
    }

    /**
     * Implementação da interface JsonSerializable
     *
     * Permite converter a entidade Cargo em formato JSON de forma segura e controlada.
     * Isso garante que apenas os atributos necessários sejam expostos ao cliente.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'idCargo' => $this->getIdCargo(),
            'nomeCargo' => $this->getNomeCargo()
        ];
    }
}
