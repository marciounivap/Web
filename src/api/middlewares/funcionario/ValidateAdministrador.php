<?php

namespace Api\Middlewares\Funcionario;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;

use Api\Http\ErrorResponse;

class ValidateAdministrador implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        error_log("🟠  ValidateAdministrador::process()");
        $payload = $request->getAttribute('jwtPayload');

        if (!$payload) {
            throw new ErrorResponse(
                401,
                "Acesso não autorizado",
                ["message" => "Usuário não autenticado"]
            );
        }

        if (!isset($payload->funcionario->role) || $payload->funcionario->role !== 'Administrador') {
            throw new ErrorResponse(
                403,
                "Acesso negado",
                ["message" => "Apenas administradores possuem acesso"]
            );
        }

        return $handler->handle($request);
    }
}