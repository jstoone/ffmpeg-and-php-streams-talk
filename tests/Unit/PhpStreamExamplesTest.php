<?php

//  STREAM
// <schema>://<path>

test('that it can read a file line-by-line', function () {
    $inputFile = storage_path('examples/input.txt');
    $fileHandle = fopen($inputFile, 'r');

    // Foreeeever, foreeeever!
    while (true) {
        // Read a line from the file
        $line = fgets($fileHandle);

        // File EOF? Break the loop
        if (feof($fileHandle)) {
            fclose($fileHandle);
            break;
        }

        dump($line);
    }

    expect($inputFile)->toBeFile();
});






test('that it can browse, rewind and copy a file', function () {
    $inputFile = storage_path('examples/input.mp4');
    $clonedFile = storage_path('examples/outputs/cloned_input.mp4');

    // print the file header
    $inputFileHandle = fopen($inputFile, 'r');


    // BROWSE
    // read the first 32 bytes of the file
    $headerIsh = fread($inputFileHandle, 32);
    dump($headerIsh);
    dump("Handle is at position: " . ftell($inputFileHandle));


    // REWIND
    dump("Rewinding the file handle.");
    rewind($inputFileHandle);
    dump("Handle is back at position: " . ftell($inputFileHandle));



    // COPY
    $clonedFileHandle = fopen($clonedFile, 'w');
    stream_copy_to_stream($inputFileHandle, $clonedFileHandle);

    // Here's when you say something about
    // - file pointer/resource
    // - file descriptors
    // - and security
    fclose($inputFileHandle);
    fclose($clonedFileHandle);

    expect(hash_file('sha256', $inputFile))
        ->toBe(hash_file('sha256', $clonedFile));
});





test('it can uppercase the stream, natively', function() {
    dump(stream_get_filters());

    $inputFile = storage_path('examples/input.txt');
    $inputFileHandle = fopen($inputFile, 'r');

    // Append/apply a filter to the stream, after opening the file
    stream_filter_append($inputFileHandle, 'string.toupper');

    // Read a line and close
    $firstLine = fgets($inputFileHandle);
    fclose($inputFileHandle);

    dump($firstLine);

    expect($firstLine)
        // ->toBeUppercase()
        ->toEqual(mb_strtoupper($firstLine));
});


test('temporary storage using `php` stream wrapper', function () {
    dump("Available wrappers:", stream_get_wrappers());
    /**
     * <schema>://<path>  <- Remember this?
     *     php://memory
     *     http://example.com
     *     file://path/to/file.txt
     */

    $tempFileHandle = fopen('php://temp', 'r+');

    foreach(range(1, 10) as $i) {
        $sentence = sprintf('Temporary line #%d added.' . PHP_EOL, $i);
        fwrite($tempFileHandle, $sentence);
    }

    rewind($tempFileHandle);

    $temporaryLines = [];
    while ($line = fgets($tempFileHandle)) {
        $temporaryLines[] = $line;
    }

    fclose($tempFileHandle);

    dump($temporaryLines);

    expect($temporaryLines)->toHaveCount(10);
});

test('it can cipher a string using a stream filter', function () {
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

test('it can register an s3 stream wrapper', function () {
    expect(stream_get_wrappers())->not->toContain('s3');

    setupAndRegisterS3StreamWrapper();

    // list all wrappers
    dump(stream_get_wrappers());
    expect(stream_get_wrappers())->toContain('s3');
});

test('it can stream a file to s3', function () {
    setupAndRegisterS3StreamWrapper();

    // create a bucket
    @mkdir('s3://bucket-of-fun', recursive: true);

    $inputFile = storage_path('examples/input.txt');
    $s3Path = 's3://my-bucket/input.txt';

    $inputFileHandle = fopen($inputFile, 'r');
    $s3FileHandle = fopen("$s3Path", 'w');

    stream_copy_to_stream($inputFileHandle, $s3FileHandle);

    fclose($inputFileHandle);
    fclose($s3FileHandle);

    expect($s3Path)->toBeFile();
});

// Well done future me!
// - Now you go back to your presentation.
