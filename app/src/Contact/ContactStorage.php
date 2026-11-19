<?php

namespace App\Contact;

class ContactStorage
{
    public const FIELDS = ['email', 'subject', 'message'];
    private string $dir;

    public function __construct()
    {
        $this->dir = dirname(__DIR__, 2) . '/var/contacts';
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0777, true);
        }
    }

    // $partial = true pour le PATCH (une ou plusieurs clés), false pour le POST (les 3 obligatoires)
    public function validate(array $data, bool $partial = false): bool
    {
        if ($data === []) return false;
        if (array_diff(array_keys($data), self::FIELDS)) return false;
        if (!$partial && array_diff(self::FIELDS, array_keys($data))) return false;
        foreach ($data as $value) {
            if (!is_string($value) || trim($value) === '') return false;
        }
        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return false;
            if (preg_match('#[/\\\\]#', $data['email'])) return false;
        }
        return true;
    }

    public function isValidFilename(string $file): bool
    {
        return $file === basename($file) && preg_match('/^\d+_.+\.json$/', $file) === 1;
    }

    public function create(array $data): ?string
    {
        $now = time();
        $file = $now . '_' . $data['email'] . '.json';
        $ok = $this->write($file, [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $now,
            'dateOfLastUpdate' => $now,
        ]);
        return $ok ? $file : null;
    }

    public function findAll(): array
    {
        $all = [];
        foreach (glob($this->dir . '/*.json') ?: [] as $path) {
            $content = json_decode(file_get_contents($path), true);
            if (is_array($content)) $all[] = $content;
        }
        return $all;
    }

    public function find(string $file): ?array
    {
        if (!$this->isValidFilename($file)) return null;
        $path = $this->dir . '/' . $file;
        if (!is_file($path)) return null;
        $content = json_decode(file_get_contents($path), true);
        return is_array($content) ? $content : null;
    }

    public function update(string $file, array $changes): ?array
    {
        $content = $this->find($file);
        if ($content === null) return null;
        $content = array_merge($content, $changes);
        $content['dateOfLastUpdate'] = time();
        return $this->write($file, $content) ? $content : null;
    }

    public function delete(string $file): bool
    {
        if ($this->find($file) === null) return false;
        return unlink($this->dir . '/' . $file);
    }

    private function write(string $file, array $content): bool
    {
        $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return @file_put_contents($this->dir . '/' . $file, $json) !== false;
    }
}
