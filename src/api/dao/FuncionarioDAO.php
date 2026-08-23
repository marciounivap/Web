<?php

namespace Api\DAO;

// Importações das classes necessárias
use Api\Models\Funcionario;               // Model/Entidade Funcionario
use Api\Models\Cargo;                     // Model/Entidade Cargo (para relacionamento)
use Api\Database\MysqlDatabase;           // Classe de conexão com o banco MySQL
use Exception;                            // Exceção genérica do PHP
use PDO;                                  // Classe nativa do PHP para acesso a banco

/**
 * Classe FuncionarioDAO (Data Access Object)
 * 
 * Responsável por realizar operações no banco de dados relacionadas à entidade Funcionario.
 * Implementa o padrão DAO, separando a lógica de acesso a dados da lógica de negócio.
 * 
 * ARQUITETURA EM CAMADAS:
 * [Controller] → [Service] → [DAO] → [Banco de Dados]
 *                           ↑
 *                      (Esta classe)
 * 
 * COMPLEXIDADES DESTE DAO:
 * - Relacionamento com a tabela cargo (JOIN)
 * - Hash de senha (segurança)
 * - Autenticação (login)
 * - Tratamento condicional no update (com/sem senha)
 */
class FuncionarioDAO
{
    /** 
     * Instância da conexão com o banco de dados
     * 
     * @var MysqlDatabase 
     */
    private MysqlDatabase $database;

    /**
     * Construtor do DAO, recebe a instância de MysqlDatabase via injeção de dependência
     *
     * @param MysqlDatabase $databaseInstance Instância da conexão com o banco
     */
    public function __construct(MysqlDatabase $databaseInstance)
    {
        // Log para debug - 🟩indica construção/instanciação
        error_log("🟩  FuncionarioDAO::__construct()");

        $this->database = $databaseInstance;
    }

    /**
     * Verifica as credenciais de um funcionário.
     *
     * @param Funcionario $funcionario
     * @return Funcionario|null
     */
    public function verificarLogin(Funcionario $funcionario): ?Funcionario
    {
        error_log("🟢 FuncionarioDAO::verificarLogin()");

        $sql = "SELECT *
        FROM funcionario
        JOIN cargo
            ON idCargo = Cargo_idCargo
        WHERE email = :email
        LIMIT 1";

        $pdo = $this->database->getConnection();

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':email' => $funcionario->getEmail()
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        /**
         * Email não encontrado.
         */
        if (!$row) {
            return null;
        }

        /**
         * Senha inválida.
         */
        if (!password_verify($funcionario->getSenha(), $row['senha'])) {
            return null;
        }

        $cargo = new Cargo();

        $cargo->setIdCargo((int) $row['idCargo']);

        $cargo->setNomeCargo($row['nomeCargo']);

        $funcionarioAutenticado = new Funcionario();

        $funcionarioAutenticado->setIdFuncionario((int) $row['idFuncionario']);

        $funcionarioAutenticado->setNomeFuncionario($row['nomeFuncionario']);

        $funcionarioAutenticado->setEmail($row['email']);

        $funcionarioAutenticado->setRecebeValeTransporte((int) $row['recebeValeTransporte']);

        $funcionarioAutenticado->setCargo($cargo);

        return $funcionarioAutenticado;
    }

    /**
     * Cria um novo funcionário no banco de dados
     * 
     * Endpoint: POST /api/v1/funcionarios (chamado pelo Service)
     * 
     * DIFERENÇAS PARA O CargoDAO::create:
     * - Mais campos (nome, email, senha, valeTransporte, cargo_id)
     * - Hash da senha com bcrypt (segurança)
     * - Uso de placeholders "?" (posicional) em vez de named placeholders
     * 
     * SEGURANÇA:
     * - password_hash() gera hash seguro da senha
     * - cost=12 define o custo computacional (quanto maior, mais seguro)
     * 
     * @param Funcionario $funcionario Objeto Funcionario com todos os dados
     * @return int ID do funcionário criado
     * @throws Exception Se a inserção falhar
     */
    public function create(Funcionario $funcionario): int
    {
        error_log("🟢 FuncionarioDAO::create()");

        // =============================================================
        // SEGURANÇA: Hash da senha antes de salvar
        // =============================================================
        // PASSWORD_BCRYPT: algoritmo de hash seguro
        // cost=12: fator de custo (quanto maior, mais lento e seguro)
        $senhaHash = password_hash($funcionario->getSenha(), PASSWORD_BCRYPT, ['cost' => 12]);

        // =============================================================
        // SQL com placeholders posicionais (?)
        // =============================================================
        $sql = "
            INSERT INTO funcionario 
            (nomeFuncionario, email, senha, recebeValeTransporte, Cargo_idCargo) 
            VALUES (?, ?, ?, ?, ?)
        ";

        // Array de parâmetros na mesma ordem dos placeholders
        $params = [
            $funcionario->getNomeFuncionario(),
            $funcionario->getEmail(),
            $senhaHash,  // Hash, não a senha original
            $funcionario->getRecebeValeTransporte(),
            $funcionario->getCargo()->getIdCargo(), // Pega ID do cargo associado
        ];

        // Obtém conexão PDO e executa
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Obtém o ID gerado (auto_increment)
        $insertId = $pdo->lastInsertId();

        if (!$insertId) {
            throw new Exception("Falha ao inserir funcionário");
        }

        return (int) $insertId;
    }

    /**
     * Remove um funcionário pelo ID
     * 
     * Endpoint: DELETE /api/v1/funcionarios/{idFuncionario}
     * 
     * @param Funcionario $funcionario Objeto Funcionario com ID definido
     * @return bool True se excluiu, False se não encontrou
     */
    public function delete(Funcionario $funcionario): bool
    {
        error_log("🟢 FuncionarioDAO::delete()");

        $sql = "DELETE FROM funcionario WHERE idFuncionario = ?";

        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$funcionario->getIdFuncionario()]);

        // rowCount() indica quantas linhas foram afetadas
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza os dados de um funcionário
     * 
     * Endpoint: PUT /api/v1/funcionarios/{idFuncionario}
     * 
     * COMPLEXIDADE:
     * - Se a senha for fornecida (não vazia), atualiza também a senha
     * - Se a senha não for fornecida, mantém a senha atual
     * 
     * Isso permite que o frontend não precise enviar a senha
     * em toda atualização (apenas quando quer mudar)
     *
     * @param Funcionario $funcionario Objeto Funcionario com dados atualizados
     * @return bool True se atualizou, False se não encontrou
     */
    public function update(Funcionario $funcionario): bool
    {
        error_log("🟢 FuncionarioDAO::update()");

        $pdo = $this->database->getConnection();

        // =============================================================
        // CASO 1: Usuário informou uma nova senha
        // =============================================================
        if (!empty($funcionario->getSenha())) {
            // Gera hash da nova senha
            $senhaHash = password_hash($funcionario->getSenha(), PASSWORD_BCRYPT, ['cost' => 12]);

            // SQL com atualização de todos os campos (incluindo senha)
            $sql = "
                UPDATE funcionario 
                SET nomeFuncionario=?, email=?, senha=?, recebeValeTransporte=?, Cargo_idCargo=? 
                WHERE idFuncionario=?
            ";
            $params = [
                $funcionario->getNomeFuncionario(),
                $funcionario->getEmail(),
                $senhaHash,  // Nova senha hasheada
                $funcionario->getRecebeValeTransporte(),
                $funcionario->getCargo()->getIdCargo(),
                $funcionario->getIdFuncionario(),
            ];
        }
        // =============================================================
        // CASO 2: Usuário NÃO informou nova senha (mantém atual)
        // =============================================================
        else {
            // SQL SEM atualização da senha
            $sql = "
                UPDATE funcionario 
                SET nomeFuncionario=?, email=?, recebeValeTransporte=?, Cargo_idCargo=? 
                WHERE idFuncionario=?
            ";
            $params = [
                $funcionario->getNomeFuncionario(),
                $funcionario->getEmail(),
                $funcionario->getRecebeValeTransporte(),
                $funcionario->getCargo()->getIdCargo(),
                $funcionario->getIdFuncionario(),
            ];
        }

        // Executa a query apropriada
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna todos os funcionários com seus respectivos cargos
     * 
     * Endpoint: GET /api/v1/funcionarios
     * 
     * DIFERENÇA PARA O CargoDAO::findAll:
     * - Faz JOIN com a tabela cargo para trazer o nome do cargo
     * - Converte os resultados em objetos (não arrays)
     * 
     * @return array Array de objetos Funcionario (cada um com seu Cargo)
     */
    public function findAll(): array
    {
        error_log("🟢 FuncionarioDAO::findAll()");

        // =============================================================
        // JOIN para trazer dados relacionados
        // =============================================================
        $sql = "
            SELECT idFuncionario, nomeFuncionario, email, recebeValeTransporte, 
                   idCargo, nomeCargo
            FROM funcionario
            JOIN cargo ON funcionario.Cargo_idCargo = idCargo
        ";

        $pdo = $this->database->getConnection();
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // =============================================================
        // Converte cada linha em objetos Cargo e Funcionario
        // =============================================================
        $result = [];
        foreach ($rows as $row) {
            // Cria objeto Cargo com os dados do JOIN
            $cargo = new Cargo();
            $cargo->setIdCargo((int) $row['idCargo']);
            $cargo->setNomeCargo($row['nomeCargo']);

            // Cria objeto Funcionario com os dados da tabela
            $funcionario = new Funcionario();
            $funcionario->setIdFuncionario((int) $row['idFuncionario']);
            $funcionario->setNomeFuncionario($row['nomeFuncionario']);
            $funcionario->setEmail($row['email']);
            $funcionario->setRecebeValeTransporte((int) $row['recebeValeTransporte']);

            // Associa o cargo ao funcionário
            $funcionario->setCargo($cargo);

            $result[] = $funcionario;
        }

        return $result;
    }

    /**
     * Retorna a quantidade total de funcionários
     * 
     * Endpoint: GET /api/v1/funcionarios/count
     * 
     * @return int Número total de funcionários
     */
    public function count(): int
    {
        error_log("🟢 FuncionarioDAO::count()");

        $sql = "SELECT COUNT(*) AS qtd FROM funcionario";

        $pdo = $this->database->getConnection();
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row['qtd'];
    }

    /**
     * Busca um funcionário específico pelo ID
     * 
     * Endpoint: GET /api/v1/funcionarios/{idFuncionario}
     * 
     * REUTILIZAÇÃO:
     * - Aproveita o método genérico findByField
     * - Retorna o primeiro resultado ou null
     *
     * @param int $idFuncionario ID do funcionário
     * @return Funcionario|null Objeto Funcionario ou null se não encontrado
     */
    public function findById(int $idFuncionario): ?Funcionario
    {
        $result = $this->findByField('idFuncionario', $idFuncionario);
        return $result[0] ?? null; // Retorna primeiro ou null
    }

    /**
     * Busca funcionários por um campo específico (método genérico)
     * 
     * @param string $field Nome do campo (deve estar em $allowedFields)
     * @param mixed $value Valor a ser buscado
     * @return array Array de objetos Funcionario
     * @throws Exception Se o campo não for permitido
     */
    public function findByField(string $field, $value): array
    {
        error_log("🟢 FuncionarioDAO::findByField() - Campo: $field, Valor: $value");

        // =============================================================
        // SEGURANÇA: Whitelist de campos permitidos
        // =============================================================
        $allowedFields = ['idFuncionario', 'nomeFuncionario', 'email', 'senha', 'recebeValeTransporte', 'Cargo_idCargo'];
        if (!in_array($field, $allowedFields)) {
            throw new Exception("Campo inválido para busca");
        }

        // =============================================================
        // Busca apenas na tabela funcionario (sem JOIN)
        // =============================================================
        $sql = "SELECT * FROM funcionario WHERE $field = ?";

        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$value]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // =============================================================
        // Converte cada linha em objeto Funcionario
        // =============================================================
        $result = [];
        foreach ($rows as $row) {
            // Cria objeto Cargo apenas com o ID (sem nome)
            $cargo = new Cargo();
            $cargo->setIdCargo((int) $row['Cargo_idCargo']);
            // NOTA: O nome do cargo não é carregado aqui (falta JOIN)

            // Cria objeto Funcionario
            $funcionario = new Funcionario();
            $funcionario->setIdFuncionario((int) $row['idFuncionario']);
            $funcionario->setNomeFuncionario($row['nomeFuncionario']);
            $funcionario->setEmail($row['email']);
            // Não setamos a senha por segurança!
            $funcionario->setRecebeValeTransporte((int) $row['recebeValeTransporte']);
            $funcionario->setCargo($cargo);

            $result[] = $funcionario;
        }

        return $result;
    }

    /**
     * Autentica um funcionário (verifica email e senha)
     * 
     * Endpoint: POST /api/v1/funcionarios/login (PÚBLICO)
     * 
     * FLUXO DE AUTENTICAÇÃO:
     * 1. Busca funcionário pelo email
     * 2. Se não encontrar → retorna null
     * 3. Se encontrar → verifica a senha com password_verify()
     * 4. Se senha válida → retorna objeto Funcionario (sem a senha)
     * 5. Se senha inválida → retorna null
     * 
     * SEGURANÇA:
     * - password_verify() compara a senha fornecida com o hash
     * - Nunca retorna a senha (nem o hash)
     * - Timing attack: mesmo tempo para usuário existente ou não
     *
     * @param Funcionario $funcionario Objeto com email e senha
     * @return Funcionario|null Objeto Funcionario se autenticado, null caso contrário
     */
    public function login(Funcionario $funcionario): ?Funcionario
    {
        error_log("🟢 FuncionarioDAO::login()");

        // =============================================================
        // Busca funcionário pelo email (com JOIN para trazer o cargo)
        // =============================================================
        $sql = "
            SELECT idFuncionario, nomeFuncionario, email, senha, recebeValeTransporte, 
                   idCargo, nomeCargo
            FROM funcionario
            JOIN cargo ON cargo.idCargo = funcionario.Cargo_idCargo
            WHERE email = ?
        ";

        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$funcionario->getEmail()]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // =============================================================
        // Caso 1: Email não encontrado
        // =============================================================
        if (!$row) {
            error_log("❌ Funcionário não encontrado");
            return null;
        }

        // =============================================================
        // Caso 2: Email encontrado, verifica senha
        // =============================================================
        if (!password_verify($funcionario->getSenha(), $row['senha'])) {
            error_log("❌ Senha inválida");
            return null;
        }

        // =============================================================
        // Caso 3: Senha válida - monta objeto de retorno
        // =============================================================
        $cargo = new Cargo();
        $cargo->setIdCargo((int) $row['idCargo']);
        $cargo->setNomeCargo($row['nomeCargo']);

        $func = new Funcionario();
        $func->setIdFuncionario((int) $row['idFuncionario']);
        $func->setNomeFuncionario($row['nomeFuncionario']);
        $func->setEmail($row['email']);
        $func->setRecebeValeTransporte((int) $row['recebeValeTransporte']);
        $func->setCargo($cargo);

        // NOTA: Não setamos a senha no objeto retornado!

        return $func;
    }
}