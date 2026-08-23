<?php

namespace Api\Middlewares\Funcionario;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

class ValidateFuncionarioLoginBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        error_log("🟠  ValidateFuncionarioLoginBody::process()");

        $body = $request->getBody()->getContents();

        $objPHP = json_decode($body);

        if (!isset($objPHP->funcionario)) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" => "O campo 'funcionario' é obrigatório!"
                ]
            );
        }

        $funcionario = $objPHP->funcionario;

        if (
            !isset($funcionario->email) ||
            empty(trim($funcionario->email))
        ) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" => "O campo 'email' é obrigatório!"
                ]
            );
        }

        if (
            !filter_var(
                $funcionario->email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" => "Email inválido!"
                ]
            );
        }

        if (
            !isset($funcionario->senha) ||
            empty(trim($funcionario->senha))
        ) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" => "O campo 'senha' é obrigatório!"
                ]
            );
        }

        return $handler->handle($request);
    }
}