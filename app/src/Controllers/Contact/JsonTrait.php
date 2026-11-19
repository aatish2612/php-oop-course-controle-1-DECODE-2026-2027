<?php

namespace App\Controllers\Contact;

use App\lib\Http\Request;
use App\lib\Http\Response;

trait JsonTrait
{
    // Renvoie le body décodé, ou false si le Content-Type n'est pas JSON ou si le JSON est invalide
    protected function readJson(Request $request): array|false
    {
        $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        if (!str_contains($headers['content-type'] ?? '', 'application/json')) {
            return false;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : false;
    }

    protected function json(int $status, mixed $data = null): Response
    {
        $response = new Response();
        $response->statusCode = $status;
        $response->content = $data === null
            ? ''
            : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $response;
    }

    // Nom du fichier demandé : ?file=xxx.json, ou /contact/xxx.json (bonus)
    protected function getFilename(Request $request): ?string
    {
        if (isset($_GET['file']) && is_string($_GET['file']) && $_GET['file'] !== '') {
            return $_GET['file'];
        }
        $last = basename((string) parse_url($request->getUri(), PHP_URL_PATH));
        return $last !== 'contact' ? urldecode($last) : null;
    }
}
