<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    setupAndRegisterS3StreamWrapper();
});

test('it can apply color filter to video', function () {
    $inputFile = storage_path('examples/input.mp4');
    $outputFile = storage_path('examples/outputs/branded_video.mp4');

    // copy everything but apply filter
    $ffmpegCommand = [
        'ffmpeg',
        // overwrite existing files
        //   and suppress output
        '-y', '-v', 'error',
        '-i', $inputFile,
        '-preset', 'ultrafast',
        '-c:a', 'copy',
        // Make grayscale
        '-vf', 'hue=s=0',
        $outputFile
    ];

    $process = Process::timeout(0)->run($ffmpegCommand);

    expect($process->successful())->toBeTrue();
});

test('it can pipe ffmpeg mp3 conversion output into php://temp', function () {
    $inputFile = storage_path('examples/input.mp4');

    $ffmpegCommand = [
        'ffmpeg',
        '-i', $inputFile,
        '-vn',
        '-map_metadata', '-1',
        '-ac', '2',
        '-acodec', 'libmp3lame',
        '-application', 'audio',
        '-b:a', '128k',
        '-compression_level', '0',
        '-f', 'mp3',
        '-v', 'error',
        '-'
    ];

    $streamedPath = 's3://bucket-of-fun/streamed.mp3';
    $streamHandle = fopen($streamedPath, 'w');

    $process = Process::quietly()->run($ffmpegCommand, function (string $type, string $buffer) use ($streamHandle) {
        if ($type === 'out') {
            fwrite($streamHandle, $buffer);
        }
    });

    expect(function () use ($process) {
        $process->output();
    })->toThrow(LogicException::class);

    fclose($streamHandle);

    expect($process->successful())->toBeTrue()
        ->and($streamedPath)->toBeFile();
});

test('it can upload ffprobe identity to s3', function () {
    $inputFile = storage_path('examples/input.mp4');
    $process = Process::run('ffprobe -v error -show_format -show_streams -print_format json '.$inputFile);

    file_put_contents("s3://bucket-of-fun/input.json", $process->output());

    dump($process->output());

    expect($process->exitCode())->toBe(0)
        ->and($process->output())->toContain('streams');
});
