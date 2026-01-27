<?php
function runMigrations($pdo) {
    $jsonFile = __DIR__ . '/migrations.json';
    
    if (!file_exists($jsonFile)) {
        file_put_contents($jsonFile, json_encode(['migrations' => []], JSON_PRETTY_PRINT));
    }
    
    $data = json_decode(file_get_contents($jsonFile), true);
    $executed = array_column($data['migrations'], 'id');
    
    $migrationsDir = __DIR__ . '/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);
    
    foreach ($files as $file) {
        $migrationId = pathinfo($file, PATHINFO_FILENAME);
        
        if (!in_array($migrationId, $executed)) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
            
            $data['migrations'][] = [
                'id' => $migrationId,
                'executed_at' => date('c')
            ];
        }
    }
    
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
}
