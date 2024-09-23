<?php

//  STREAM
// <schema>://<path>
test('we can see multiple stream wrappers', function () {
    expect(stream_get_wrappers())
        ->toContain('file')
        ->dump();
});














test('we can read a file line-by-line', function () {
    // [file://]SHOME/$PROJECT/storage/examples/input.txt
    $inputFile = storage_path('examples/input.txt');
    $inputFileHandle = fopen($inputFile, 'r');

    // Foreeeever, foreeeever!
    while (true) {
        // Read a line from the file
        $line = fgets($inputFileHandle);

        // File EOF? Break the loop
        if (feof($inputFileHandle)) {
            fclose($inputFileHandle);
            break;
        }

        dump($line);
    }

    expect($inputFile)->toBeFile();
});






test('we can browse, rewind and copy a file', function () {
    $inputFile = storage_path('examples/input.mp4');
    $outputFile = storage_path('examples/outputs/cloned_input.mp4');

    $inputFileHandle = fopen($inputFile, 'r');
    $outputFileHandle = fopen($outputFile, 'w');

    // BROWSE
    dump(
        // read the first 32 bytes of the file
        fread($inputFileHandle, 32),
        "Handle pointer is at position: " . ftell($inputFileHandle)
    );

    // REWIND
     dump(
        "Rewinding the file handle.",
        rewind($inputFileHandle),
        "Handle pointer is back at position: " . ftell($inputFileHandle),
     );

    // COPY
    stream_copy_to_stream($inputFileHandle, $outputFileHandle);

    // Here's when you say something about
    // - file pointer/resource
    // - file descriptors
    // - and security
    fclose($inputFileHandle);
    fclose($outputFileHandle);

    expect(hash_file('sha256', $inputFile))
        ->toBe(hash_file('sha256', $outputFile));
});





test('we can uppercase the stream, natively', function() {
    dump(stream_get_filters());

    $inputFile = storage_path('examples/input.txt');
    $inputFileHandle = fopen($inputFile, 'r');

    // Append/apply a filter to the stream, after opening the file
    stream_filter_append($inputFileHandle, 'string.toupper');

    // Read a line and close
    $firstLine = fgets($inputFileHandle);
    fclose($inputFileHandle);

    expect($firstLine)
        // ->toBeUppercase()
        ->toEqual(mb_strtoupper($firstLine))
        ->dump();
});





/**
 * <schema>://<path>  <- Remember this?
 *  http://example.com
 *  file://path/to/file.txt
 *  php://memory
 *  php://temp
 *  php://stdin
 *  php://stdout
 *  php://stderr
 *  php://input
 *  php://output
 */
test('we can use temporary storage via the `php` stream wrapper', function () {
    dump("Available wrappers:", stream_get_wrappers());

    // php:://temp/maxmemory:4096
    $tempFileHandle = fopen('php://temp', 'r+');

    foreach(range(1, 10) as $i) {
        $sentence = sprintf('Temporary line #%d added.' . PHP_EOL, $i);
        fwrite($tempFileHandle, $sentence);
    }

    rewind($tempFileHandle);

    $temporaryLines = "";
    while ($line = fgets($tempFileHandle)) {
        $temporaryLines .= $line;
    }

    dump($temporaryLines);

    fclose($tempFileHandle);

    // expect to contain 10 \n characters
    expect(substr_count($temporaryLines, PHP_EOL))->toEqual(10);
});

test('we can cipher a string using a stream filter', function () {
    $inputString = 'The treasure is buried by the old oak tree.';
    $cipherFile = storage_path('examples/outputs/treasure.cipher');

    file_put_contents("php://filter/write=string.rot13/resource=$cipherFile", $inputString);

    $cipherString = file_get_contents($cipherFile);

    dump('Ciphered string:', $cipherString);

    expect($cipherFile)->toBeFile()
        ->and(
            str_rot13($cipherString)
        )->toBe($inputString);
});

test('we can register an s3 stream wrapper', function () {
    expect(stream_get_wrappers())->not->toContain('s3');

    setupS3ClientAndRegisterStreamWrapper();

    // list all wrappers
    expect(stream_get_wrappers())
        ->toContain('s3')
        ->dump();
});

test('it can stream a file to s3', function () {
    setupS3ClientAndRegisterStreamWrapper();

    // create a bucket
    @mkdir('s3://bucket-of-fun');

    $inputFile = storage_path('examples/input.txt');
    $outputFile = 's3://my-bucket/input.txt';

    $inputFileHandle = fopen($inputFile, 'r');
    $outputFileHandle = fopen($outputFile, 'w');

    stream_copy_to_stream($inputFileHandle, $outputFileHandle);

    fclose($inputFileHandle);
    fclose($outputFileHandle);

    expect($outputFile)->toBeFile();
});

// Well done future me!
// - Now you go back to your presentation.
