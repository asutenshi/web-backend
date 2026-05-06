<?php
// Из имени файла (например, picture.jpg) получите его расширение (например, jpg)
function get_file_name_ext($file_name) {
    $pattern = '/\.([a-z0-9]+)$/i';

    if (preg_match($pattern, $file_name, $matches)) {
        return $matches[1];
    }

    return null;
}

echo get_file_name_ext('image.png');
echo '<br>';
echo get_file_name_ext('music.mp3');

echo '<br>';
echo '<br>';


// По имени файла проверьте соответствует ли оно: а) архиву, б) аудиофайлу, в) видеофайлу, г) картинке
function get_file_type($file_name) {
    $file_ext = get_file_name_ext($file_name);

    if (!$file_ext) {
        return 'Не удалось определить расширение файла';
    }

    if (preg_match('/^(zip|rar|7z|tar|gz)$/i', $file_ext)) {
        return 'Это архив';
    }
    
    if (preg_match('/^(mp3|wav|ogg|flac)$/i', $file_ext)) {
        return 'Это аудиофайл';
    }   
    
    if (preg_match('/^(mp4|avi|mkv|mov)$/i', $file_ext)) {
        return 'Это видеофайл';
    }
    
    if (preg_match('/^(jpg|jpeg|png|gif|webp)$/i', $file_ext)) {
        return 'Это картинка';
    }

    return 'Неизвестный тип файла';
}

echo get_file_type('image.png');
echo '<br>';
echo get_file_type('music.mp3');

echo '<br>';
echo '<br>';

// В произвольном HTML-коде найдите строку, заключенную в теги <title></title>
function get_text_from_title_tag($text) {
    $pattern = '/<title>(.*?)<\/title>/is';

    if (preg_match($pattern, $text, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

$html_code = "
<html>
    <head>
        <title> 
            Моя крутая страница 
        </title>
    </head>
    <body>...</body>
</html>";


echo get_text_from_title_tag($html_code);

echo '<br>';
echo '<br>';

// В произвольном HTML-коде найдите все ссылки в тегах <a> (атрибут href)
function get_links($text) {
    $pattern = '/<a\s+[^>]*href=(["\'])(.*?)\1[^>]*>/is';

    if (preg_match_all($pattern, $text, $matches)) {
        return $matches[2];
    }
    
    return [];
}

$html_code = '
    <div class="menu">
        <a href="https://google.com">Google</a>
        <a class="active" href=\'/page1\'>First Page</a>
        <a href="contact.php" target="_blank">Contacts</a>
    </div>
';

$links = get_links($html_code);

print_r($links);

echo '<br>';
echo '<br>';

// В произвольном HTML-коде найдите все ссылки на картинки в тегах <img> (атрибут src)
function get_imgs($text) {
    $pattern = '/<img\s+[^>]*src=(["\'])(.*?)\1[^>]*>/is';

    if (preg_match_all($pattern, $text, $matches)) {
        return $matches[2];
    }
    
    return [];
}

$html_code = '
    <div class="gallery">
        <img src="images/logo.png" alt="Logo">
        <img class="preview" src=\'https://site.com/photo.jpg\' />
        <img id="banner" src="banners/summer_sale.gif">
    </div>
';

$images = get_imgs($html_code);

print_r($images);

echo '<br>';
echo '<br>';

// В произвольном тексте найдите и подсветите с помощью тега <strong> заданную строку
function highlight_phrase($text, $phrase) {
    $quoted_phrase = preg_quote($phrase, '/');
    $pattern = '/' . $quoted_phrase . '/ui';

    $replacement = '<strong>$0</strong>';

    return preg_replace($pattern, $replacement, $text);
}

$my_text = "PHP — это отличный язык, а регулярные выражения в PHP — это мощь.";
$search = "php";

echo highlight_phrase($my_text, $search);

echo '<br>';
echo '<br>';

// В произвольном тексте найдите определенный набор текстовых смайликов :), ;), :(на соответствующие им картинки <img src="smile.png" alt=":)">, <img src="wink.png" alt=";)">, <img src="sad.png" alt=":(">
function smile_to_image($text) {
    $patterns = [
        '/:\)/',
        '/;\)/',
        '/:\(/'
    ];

    $replacements = [
        '<img src="smile.png" alt=":)">',
        '<img src="wink.png" alt=";)">',
        '<img src="sad.png" alt=":(">',
    ];

    return preg_replace($patterns, $replacements, $text);
}

$input_text = "Привет! Как дела? :) Я вчера купил новую игру ;) Но она оказалась скучной :(";

echo smile_to_image($input_text);

echo '<br>';
echo '<br>';

// В заданной строке избавьтесь от случайных повторяющихся пробелов.
function remove_doub_spaces($text) {
    $pattern = '/\s{2,}/';

    return preg_replace($pattern, ' ', $text);
}

$input = "Это    текст   с огромным      количеством лишних   пробелов.";
echo remove_doub_spaces($input);