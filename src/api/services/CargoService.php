<?php

namespace Api\Services;

use Api\Models\Cargo;
use Api\DAO\CargoDAO;
use Api\Http\ErrorResponse;
use stdClass;

/**
 * Camada de regra de negócio da entidade Cargo.
 *
 * Fluxo:
 * Controller -> Service -> DAO -> Banco
 */
class CargoService
{
    /**
     * DAO responsável pelo acesso aos dados.
     *
     * @var CargoDAO
     */
    private CargoDAO $cargoDAO;

    /**
     * Injeção de dependência.
     *
     * @param CargoDAO $cargoDAODependency
     */
    public function __construct(CargoDAO $cargoDAODependency)
    {
  
        error_log("🟪 CargoService::__construct()");
        $this->cargoDAO = $cargoDAODependency;
    }

    /**
     * Cria um novo cargo.
     *
     * Regras:
     * - Não permite nome duplicado.
     *
     * @param array $objPHP
     * @return Cargo
     * @throws ErrorResponse
     */
    public function createService(stdClass $objPHP): Cargo
    {
        error_log("🟣 CargoService::createService()");

        $cargo = new Cargo();
        $cargo->setNomeCargo($objPHP->cargo->nomeCargo);

        /**
         * Verifica duplicidade.
         */
        $resultado = $this->cargoDAO->findByField(
            'nomeCargo',
            $cargo->getNomeCargo()
        );

        if (count($resultado) > 0) {
            throw new ErrorResponse(
                400,
                "Cargo já existe",
                [
                    "message" =>
                        "O cargo {$cargo->getNomeCargo()} já existe"
                ]
            );
        }

        return $this->cargoDAO->create($cargo);
    }

    /**
     * Retorna quantidade total.
     *
     * @return int
     */
    public function countService(): int
    {
        error_log("🟣 CargoService::countService()");
        return $this->cargoDAO->count();
    }

    /**
     * Lista todos os cargos.
     *
     * @return array
     */
    public function findAllService(): array
    {
        error_log("🟣 CargoService::findAllService()");
        return $this->cargoDAO->findAll();
    }

    /**
     * Busca cargo por ID.
     *
     * @param int $idCargo
     * @return Cargo|null
     */
    public function findByIdService(int $idCargo): ?Cargo
    {
        error_log("🟣 CargoService::findByIdService()");

        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);

        return $this->cargoDAO->findById(
            $cargo->getIdCargo()
        );
    }

    /**
     * Atualiza cargo existente.
     *
     * Regras:
     * - O cargo precisa existir.
     * - Se não existir, lança erro 404.
     *
     * @param int $idCargo
     * @param string $nomeCargo
     * @return bool
     * @throws ErrorResponse
     */
    public function updateService(int $idCargo, string $nomeCargo): bool
    {
        error_log("🟣 CargoService::updateService()");

        /**
         * Verifica existência.
         */
        $cargoExistente = $this->cargoDAO->findById($idCargo);

        if (!$cargoExistente) {
            throw new ErrorResponse(
                404,
                "Cargo não encontrado",
                [
                    "message" =>
                        "Não existe cargo com id {$idCargo}"
                ]
            );
        }

        /**
         * Monta objeto atualizado.
         */
        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);
        $cargo->setNomeCargo($nomeCargo);

        return $this->cargoDAO->update($cargo);
    }

    /**
     * Remove cargo existente.
     *
     * Regras:
     * - O cargo precisa existir.
     * - Se não existir, lança erro 404.
     *
     * @param int $idCargo
     * @return bool
     * @throws ErrorResponse
     */
    public function deleteService(int $idCargo): bool
    {
        error_log("🟣 CargoService::deleteService()");

        /**
         * Verifica existência.
         */
        $cargoExistente = $this->cargoDAO->findById($idCargo);

        if (!$cargoExistente) {
            throw new ErrorResponse(
                404,
                "Cargo não encontrado",
                [
                    "message" =>
                        "Não existe cargo com id {$idCargo}"
                ]
            );
        }

        /**
         * Monta objeto para exclusão.
         */
        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);

        return $this->cargoDAO->delete($cargo);
    }
}