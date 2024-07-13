<?php

namespace Core\Http;

use Core\Database;

class DatabaseSessionHandler implements \SessionHandlerInterface
{

    public function __construct(
        private Database $db
    ) {
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId): string
    {
        $query = 'SELECT data FROM sessions WHERE id = :id';

        $data = $this
            ->db
            ->query($query, ['id' => $sessionId])
            ->fetch();

        return $data ? $data['data'] : '';
    }

    public function write(string $sessionId, string $data): bool
    {

        $query = 'REPLACE INTO sessions (id, data, last_access) VALUES (:id, :data, NOW())';
        $params = [
            'id' => $sessionId,
            'data' => $data,
        ];

        $this->db->query($query, $params);

        return true;
    }

    public function destroy($sessionId): bool
    {
        $query = 'DELETE FROM sessions WHERE id = :id';
        $params = ['id' => $sessionId];

        $this->db->query($query, $params);

        return true;
    }

    public function gc($maxLifetime): bool
    {
        $lastAccess = date('Y-m-d H:i:s', time() - $maxLifetime);
        $query = 'DELETE FROM sessions WHERE last_access < :lastAccess';
        $this->db->query($query, compact('lastAccess'));

        return true;
    }
}
