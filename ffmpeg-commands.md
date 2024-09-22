### FFMpeg commands

#### Video

**720p optimized for web**:

```bash
ffmpeg -i input.mp4 \
    -c:v libx264
    -preset fast \
    -crf 23 \
    -b:v 3000k \
    -maxrate 3500k \
    -bufsize 1835k \
    -vf "scale=-2:720" \
    -c:a aac \
    -b:a 128k \
    -r 30 \
    -movflags +faststart \
    output_video.mp4

ffmpeg -i input.mp4 -vf "scale=1280:-1" -c:v libx264 -crf 23 -c:a aac -b:a 128k -f mp4 output_720p.mp4
```

```php
    $ffmpegCommand = "ffmpeg -i $inputFile -f webm -c:v vp9 -c:a libopus pipe:1";
```

**Deep Fry**:

```bash
ffmpeg -i input.mp4 -vf "eq=contrast=3:saturation=5, noise=alls=20:allf=t+u" -b:v 200k output_deepfried.mp4
```

**VHS**:

```bash
ffmpeg -i input.mp4 -vf "curves=vintage" -c:a copy output_vhs.mp4
```

#### Audio

**Lo-fi Nightmare**:

```bash
ffmpeg -i input.wav -ar 8000 -af "aecho=0.8:0.88:60:0.4, highpass=f=3000, lowpass=f=800" output_lofi.wav
```
