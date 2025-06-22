<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear 
                            {--days=30 : Keep logs newer than this many days}
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old log files to free up disk space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $force = $this->option('force');
        
        $this->info("🗑️  Clearing log files older than {$days} days...");
        
        $logPaths = [
            storage_path('logs'),
            storage_path('logs/backup.log'),
            storage_path('logs/monitoring.log'),
        ];
        
        $totalSize = 0;
        $totalFiles = 0;
        $cutoffDate = Carbon::now()->subDays($days);
        
        foreach ($logPaths as $path) {
            if (is_dir($path)) {
                $files = File::files($path);
                
                foreach ($files as $file) {
                    $fileTime = Carbon::createFromTimestamp($file->getMTime());
                    
                    if ($fileTime->lt($cutoffDate)) {
                        $size = $file->getSize();
                        
                        if ($force || $this->confirm("Delete {$file->getFilename()} ({$this->formatBytes($size)})?")) {
                            File::delete($file->getPathname());
                            $totalSize += $size;
                            $totalFiles++;
                            $this->info("✅ Deleted: {$file->getFilename()}");
                        }
                    }
                }
            } elseif (is_file($path)) {
                $fileTime = Carbon::createFromTimestamp(filemtime($path));
                
                if ($fileTime->lt($cutoffDate)) {
                    $size = filesize($path);
                    
                    if ($force || $this->confirm("Delete " . basename($path) . " ({$this->formatBytes($size)})?")) {
                        unlink($path);
                        $totalSize += $size;
                        $totalFiles++;
                        $this->info("✅ Deleted: " . basename($path));
                    }
                }
            }
        }
        
        if ($totalFiles > 0) {
            $this->info("🎉 Cleaned up {$totalFiles} files, freed {$this->formatBytes($totalSize)}");
        } else {
            $this->info("✨ No old log files found to clean up");
        }
        
        return 0;
    }
    
    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
