<?php

namespace Api\Services;

use Api\DAO\CargoDAO;
use Api\DAO\FuncionarioDAO;
use Api\Models\Cargo;
use Api\Models\Funcionario;
use Api\Http\MeuTokenJWT;
use Api\Http\ErrorResponse;

/**
 * Camada de regra de negócio da entidade Funcionário.
 *
 * Fluxo:
 * Controller -> Service -> DAO -> Banco
 */
class FuncionarioService
{
    /**
     * DAO de funcionário.
     *
     * @var FuncionarioDAO
     */
    private FuncionarioDAO $funcionarioDAO;

    /**
     * DAO de cargo.
     *
     * @var CargoDAO
     */
    private CargoDAO $cargoDAO;

    /**
     * Injeção de dependência.
     *
     * @param FuncionarioDAO $funcionarioDAODependency
     * @param CargoDAO $cargoDAODependency
     */
    public function __construct(FuncionarioDAO $funcionarioDAODependency, CargoDAO $cargoDAODependency)
    {
        error_log("🟪 FuncionarioService::__construct()");

        $this->funcionarioDAO = $funcionarioDAODependency;
        $this->cargoDAO = $cargoDAODependency;
    }


    /**
     * Realiza o login do funcionário.
     *
     * Fluxo:
     * 1. Valida email e senha recebidos.
     * 2. Solicita ao DAO a autenticação.
     * 3. Se autenticado, gera o JWT.
     * 4. Retorna funcionário e token.
     *
     * @param array $jsonFuncionario
     * @return array
     * @throws ErrorResponse
     */
    public function loginService(array $jsonFuncionario): array
    {

        error_log("🟣 FuncionarioService::loginService()");

        $funcionario = new Funcionario();
        $funcionario->setEmail($jsonFuncionario['email']);
        $funcionario->setSenha($jsonFuncionario['senha']);
        /**
         * Solicita autenticação ao DAO.
         */
        $funcionario = $this->funcionarioDAO->verificarLogin($funcionario);


        /**
         * Funcionário não autenticado.
         */
        if (!$funcionario) {

            throw new ErrorResponse(
                401,
                "Usuário ou senha inválidos",
                [
                    "message" =>
                        "Não foi possível autenticar o funcionário"
                ]
            );
        }

        /**
         * Instância responsável pela geração do JWT.
         */
        $jwt = new MeuTokenJWT();

        /**
         * Claims utilizadas na geração do token.
         */
        $claims = new \stdClass();

        $claims->idFuncionario = $funcionario->getIdFuncionario();

        $claims->name = $funcionario->getNomeFuncionario();

        $claims->email = $funcionario->getEmail();

        $claims->role = $funcionario->getCargo()?->getNomeCargo();

        /**
         * Gera o token JWT.
         */
        $token = $jwt->gerarToken($claims);

        /**
         * Retorna os dados necessários
         * para a autenticação.
         */
        return [
            'funcionario' => $funcionario,
            'token' => $token
        ];
    }
    /**
     * Cria novo funcionário.
     *
     * Regras:
     * - Cargo informado deve existir.
     * - Email não pode estar duplicado.
     *
     * @param array $jsonFuncionario
     * @return Funcionario
     * @throws ErrorResponse
     */
    public function createService(array $jsonFuncionario): Funcionario
    {
        error_log("🟣 FuncionarioService::createService()");

        /**
         * Cargo informado.
         */
        $cargo = new Cargo();
        $cargo->setIdCargo($jsonFuncionario['cargo']['idCargo']);

        /**
         * Verifica se cargo existe.
         */
        $cargoExiste = $this->cargoDAO->findById($cargo->getIdCargo());

        if (!$cargoExiste) {
            throw new ErrorResponse(
                404,
                "Cargo não encontrado",
                [
                    "message" =>
                        "Não existe cargo com id {$cargo->getIdCargo()}"
                ]
            );
        }

        /**
         * Funcionário.
         */
        $funcionario = new Funcionario();
        $funcionario->setNomeFuncionario($jsonFuncionario['nomeFuncionario']);
        $funcionario->setEmail($jsonFuncionario['email']);
        $funcionario->setSenha($jsonFuncionario['senha']);
        $funcionario->setRecebeValeTransporte(
            $jsonFuncionario['recebeValeTransporte']
        );
        $funcionario->setCargo($cargoExiste);

        /**
         * Verifica email duplicado.
         */
        $emailExiste = $this->funcionarioDAO->findByField('email', $funcionario->getEmail());

        if (count($emailExiste) > 0) {
            throw new ErrorResponse(
                400,
                "Email já cadastrado",
                [
                    "message" =>
                        "O email {$funcionario->getEmail()} já existe"
                ]
            );
        }

        /**
         * Salva.
         */
        $idCriado = $this->funcionarioDAO->create($funcionario);

        $funcionario->setIdFuncionario($idCriado);

        return $funcionario;
    }



    /**
     * Lista todos os funcionários.
     *
     * @return array
     */
    public function findAll(): array
    {
        error_log("🟣 FuncionarioService::findAll()");
        return $this->funcionarioDAO->findAll();
    }

    /**
     * Busca funcionário por ID.
     *
     * @param int $idFuncionario
     * @return Funcionario
     * @throws ErrorResponse
     */
    public function findByIdService(
        int $idFuncionario
    ): Funcionario {
        error_log("🟣 FuncionarioService::findByIdService()");

        $funcionario = $this->funcionarioDAO->findById(
            $idFuncionario
        );

        if (!$funcionario) {
            throw new ErrorResponse(
                404,
                "Funcionário não encontrado",
                [
                    "message" =>
                        "Não existe funcionário com id {$idFuncionario}"
                ]
            );
        }

        return $funcionario;
    }

    /**
     * Atualiza funcionário existente.
     *
     * Regras:
     * - Funcionário deve existir.
     * - Cargo informado deve existir.
     *
     * @param int $idFuncionario
     * @param array $requestBody
     * @return bool
     * @throws ErrorResponse
     */
    public function updateService(
        int $idFuncionario,
        array $requestBody
    ): bool {
        error_log("🟣 FuncionarioService::updateService()");

        /**
         * Verifica funcionário.
         */
        $funcionarioExiste =
            $this->funcionarioDAO->findById(
                $idFuncionario
            );

        if (!$funcionarioExiste) {
            throw new ErrorResponse(
                404,
                "Funcionário não encontrado",
                [
                    "message" =>
                        "Não existe funcionário com id {$idFuncionario}"
                ]
            );
        }

        $jsonFuncionario =
            $requestBody['funcionario'];

        /**
         * Verifica cargo.
         */
        $cargo = $this->cargoDAO->findById(
            $jsonFuncionario['cargo']['idCargo']
        );

        if (!$cargo) {
            throw new ErrorResponse(
                404,
                "Cargo não encontrado",
                [
                    "message" =>
                        "Cargo informado não existe"
                ]
            );
        }

        /**
         * Monta objeto atualizado.
         */
        $funcionario = new Funcionario();
        $funcionario->setIdFuncionario(
            $idFuncionario
        );
        $funcionario->setNomeFuncionario(
            $jsonFuncionario['nomeFuncionario']
        );
        $funcionario->setEmail(
            $jsonFuncionario['email']
        );
        $funcionario->setSenha(
            $jsonFuncionario['senha']
        );
        $funcionario->setRecebeValeTransporte(
            $jsonFuncionario['recebeValeTransporte']
        );
        $funcionario->setCargo($cargo);

        return $this->funcionarioDAO->update(
            $funcionario
        );
    }

    /**
     * Remove funcionário existente.
     *
     * Regras:
     * - Funcionário deve existir.
     *
     * @param int $idFuncionario
     * @return bool
     * @throws ErrorResponse
     */
    public function deleteService(
        int $idFuncionario
    ): bool {
        error_log("🟣 FuncionarioService::deleteService()");

        $funcionarioExiste =
            $this->funcionarioDAO->findById(
                $idFuncionario
            );

        if (!$funcionarioExiste) {
            throw new ErrorResponse(
                404,
                "Funcionário não encontrado",
                [
                    "message" =>
                        "Não existe funcionário com id {$idFuncionario}"
                ]
            );
        }

        $funcionario = new Funcionario();
        $funcionario->setIdFuncionario(
            $idFuncionario
        );

        return $this->funcionarioDAO->delete(
            $funcionario
        );
    }

    /**
     * Retorna total de funcionários.
     *
     * @return int
     */
    public function countService(): int
    {
        error_log("🟣 FuncionarioService::countService()");
        return $this->funcionarioDAO->count();
    }
}