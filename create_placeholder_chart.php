<?php
// Create a simple placeholder chart image
$width = 600;
$height = 400;
$image = imagecreatetruecolor($width, $height);

// Set background color (light gray)
$bgColor = imagecolorallocate($image, 245, 245, 245);
imagefill($image, 0, 0, $bgColor);

// Create colors
$gridColor = imagecolorallocate($image, 220, 220, 220);
$barColor = imagecolorallocate($image, 164, 152, 133); // #A49885
$textColor = imagecolorallocate($image, 70, 70, 70);

// Draw grid lines
for ($i = 50; $i < $height; $i += 50) {
    imageline($image, 50, $i, $width - 50, $i, $gridColor);
}

for ($i = 50; $i < $width; $i += 50) {
    imageline($image, $i, 50, $i, $height - 50, $gridColor);
}

// Draw frame
imagerectangle($image, 50, 50, $width - 50, $height - 50, $gridColor);

// Draw bars (sample data)
$barWidth = 40;
$data = [120, 80, 160, 200, 100, 180, 140];
$numBars = count($data);
$spacing = ($width - 100) / ($numBars + 1);

for ($i = 0; $i < $numBars; $i++) {
    $x = 50 + ($i + 1) * $spacing - $barWidth/2;
    $barHeight = $data[$i];
    $y = $height - 50 - $barHeight;
    imagefilledrectangle($image, $x, $y, $x + $barWidth, $height - 50, $barColor);
}

// Add text
$text = "Placeholder Chart";
imagettftext($image, 14, 0, $width/2 - 70, 30, $textColor, "arial.ttf", $text);

// Save the image
if (imagepng($image, 'assets/images/chart_placeholder.png')) {
    echo "Placeholder chart created successfully!";
} else {
    echo "Failed to create placeholder chart.";
}

// Clean up
imagedestroy($image);
?>
