<?php

$im = imagecreatetruecolor(360, 360);

imagealphablending($im, TRUE);

$color = (isset($_GET['color']) AND is_color($_GET['color'])) ? $_GET['color'] : '000000';
$rgb = hex2rgb(format_color_hex($color));
$bg = imagecolorallocate($im, $rgb->r, $rgb->g, $rgb->b);

imagefilledrectangle($im, 0, 0, 360, 360, $bg);

$bgr = ($bg >> 16) & 0xFF;
$bgg = ($bg >> 8) & 0xFF;
$bgb = $bg & 0xFF;

$logo = imagecreatefrompng('apple-touch-icon.png');

for($x = 0; $x < 360; ++$x)
{
    for($y = 0; $y < 360; ++$y)
    {
        $c = imagecolorat($logo, $x, $y);
        $r = ($c >> 16) & 0xFF;
        $g = ($c >> 8) & 0xFF;
        $b = $c & 0xFF;
        $r1 = abs($r - $bgr);
        $g1 = abs($g - $bgg);
        $b1 = abs($b - $bgb);
        $n = imagecolorallocate($im, $r1, $g1, $b1);
        imagesetpixel($im, $x, $y, $n);
    }
}

header('Content-type: image/png');

imagepng($im);
imagedestroy($im);


function hex2rgb($color)
{
    return (object) array('r' => hexdec($color{0} . $color{1}), 'g' => hexdec($color{2} . $color{3}), 'b' => hexdec($color{4} . $color{5}));
}

function rgb2hex($r, $g, $b)
{
    return str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

function format_color_css($color)
{
    $color = strtolower($color);

    if(in_array($color, array('ff0000', 'f00', 'red')))
    {
        return 'red';
    }
    elseif(preg_match('/^([a-f0-9]{3})$/i', $color))
    {
        return ('#' . $color);
    }
    elseif(preg_match('/^([a-f0-9]{6})$/i', $color))
    {
        if($color{0} == $color{1} AND $color{2} == $color{3} AND $color{4} == $color{5})
        {
            return ('#' . $color{1} . $color{3} . $color{5});
        }

        return ('#' . $color);
    }
    elseif(is_color($color, 'svg'))
    {
        $colors = get_colors();

        return format_color_css($colors[$color]);
    }

    return FALSE;
}

function format_color_hex($color)
{
    if(preg_match('/^([a-f0-9]{6})$/i', $color))
    {
        return $color;
    }
    elseif(preg_match('/^([a-f0-9]{3})$/i', $color))
    {
        return $color{0} . $color{0} . $color{1} . $color{1} . $color{2} . $color{2};
    }
    elseif(is_color($color, 'svg'))
    {
        $colors = get_colors();

        return $colors[$color];
    }

    return FALSE;
}

function get_colors()
{
    $colors = explode("\n", file_get_contents('svg.txt'));
    $new_colors = array();

    foreach($colors as $key => $value)
    {
        $c = explode('#',$value);
        $new_colors[trim($c[0])] = strtolower(trim($c[1]));
    }

    return $new_colors;
}

function is_color($color = null, $type = 'all')
{
    switch($type)
    {
        case '#':
        case 'hex':
            return preg_match('/^(([a-f0-9]{3})|([a-f0-9]{6}))$/i', $color);
            break;
        case 'css':
        case 'svg':
        case 'svg1':
            return array_key_exists($color, get_colors());
            break;
        case 'all':
        default:
            return (is_color($color, 'hex') OR is_color($color, 'svg'));
    }
}



?>