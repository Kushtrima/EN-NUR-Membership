<?php

// Minimal Laravel bootstrap test
try {
    echo "1. Starting Laravel bootstrap test...<br>";
    
    // Test autoloader
    echo "2. Testing autoloader...<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "3. Autoloader loaded successfully!<br>";
    
    // Test Laravel app creation
    echo "4. Testing Laravel app creation...<br>";
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "5. Laravel app created successfully!<br>";
    
    // Test kernel
    echo "6. Testing HTTP kernel...<br>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "7. HTTP kernel created successfully!<br>";
    
    echo "8. Laravel bootstrap completed successfully!<br>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "Laravel Version: " . app()->version() . "<br>";
    
} catch (Exception $e) {
    echo "<h2>ERROR FOUND:</h2>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Stack Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>FATAL ERROR FOUND:</h2>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Stack Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
}
?> 