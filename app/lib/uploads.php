<?php
declare(strict_types=1);

const IMAGE_MAX_BYTES = 12 * 1024 * 1024;
const IMAGE_MAX_PIXELS = 30_000_000;
const PDF_MAX_BYTES = 25 * 1024 * 1024;
const THUMB_WIDTH = 640;

function upload_present(?array $file): bool
{
    return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

function upload_check(array $file, int $maxBytes): void
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('Fichier trop volumineux.');
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Le téléversement a échoué.');
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Fichier trop volumineux (maximum ' . human_size($maxBytes) . ').');
    }
}

/**
 * Valide puis ré-encode l'image avec GD. Le ré-encodage supprime les métadonnées
 * (dont la géolocalisation des photos de terrain) et neutralise les fichiers piégés.
 * Retourne le chemin relatif au dossier uploads.
 */
function process_image_upload(array $file, int $maxWidth = 1920): string
{
    upload_check($file, IMAGE_MAX_BYTES);
    $info = @getimagesize($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!$info || !isset($allowed[$info['mime']])) {
        throw new RuntimeException('Format non accepté. Utilisez JPG, PNG ou WebP.');
    }
    if ($info[0] * $info[1] > IMAGE_MAX_PIXELS) {
        throw new RuntimeException('Image trop grande (30 mégapixels maximum).');
    }
    ini_set('memory_limit', '512M');
    $src = match ($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png' => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
    };
    if (!$src) {
        throw new RuntimeException('Image illisible ou corrompue.');
    }
    if ($info['mime'] === 'image/jpeg') {
        $src = apply_exif_orientation($src, $file['tmp_name']);
    }

    $ext = $allowed[$info['mime']];
    $dir = 'images/' . date('Y/m');
    if (!is_dir(UPLOADS . '/' . $dir) && !mkdir(UPLOADS . '/' . $dir, 0755, true) && !is_dir(UPLOADS . '/' . $dir)) {
        throw new RuntimeException('Impossible de créer le dossier de destination.');
    }
    $relative = $dir . '/' . bin2hex(random_bytes(12)) . '.' . $ext;

    save_resized($src, $maxWidth, UPLOADS . '/' . $relative, $ext);
    save_resized($src, THUMB_WIDTH, UPLOADS . '/' . thumb_path($relative), $ext);
    imagedestroy($src);
    return $relative;
}

function apply_exif_orientation(GdImage $img, string $path): GdImage
{
    if (!function_exists('exif_read_data')) {
        return $img;
    }
    $exif = @exif_read_data($path);
    $angle = match ((int) ($exif['Orientation'] ?? 1)) {
        3 => 180,
        6 => -90,
        8 => 90,
        default => 0,
    };
    if ($angle === 0) {
        return $img;
    }
    $rotated = imagerotate($img, $angle, 0);
    if ($rotated) {
        imagedestroy($img);
        return $rotated;
    }
    return $img;
}

function save_resized(GdImage $src, int $maxWidth, string $path, string $ext): void
{
    $w = imagesx($src);
    $h = imagesy($src);
    $nw = min($w, $maxWidth);
    $nh = (int) max(1, round($h * $nw / $w));
    $dst = imagecreatetruecolor($nw, $nh);
    if ($ext !== 'jpg') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = match ($ext) {
        'jpg' => imagejpeg($dst, $path, 82),
        'png' => imagepng($dst, $path, 8),
        'webp' => imagewebp($dst, $path, 82),
    };
    imagedestroy($dst);
    if (!$ok) {
        throw new RuntimeException('Impossible d\'enregistrer l\'image.');
    }
}

function thumb_path(string $path): string
{
    return preg_replace('/\.(\w+)$/', '_t.$1', $path) ?? $path;
}

function delete_image(?string $path): void
{
    if (!$path || str_contains($path, '..')) {
        return;
    }
    foreach ([$path, thumb_path($path)] as $p) {
        $full = UPLOADS . '/' . $p;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

/**
 * Les PDF sont stockés hors de la racine web (storage/docs) et servis par le contrôleur,
 * ce qui permet de contrôler la publication et de compter les téléchargements.
 */
function process_pdf_upload(array $file): array
{
    upload_check($file, PDF_MAX_BYTES);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 1024);
    if ($mime !== 'application/pdf' || !str_contains($head, '%PDF-')) {
        throw new RuntimeException('Le fichier doit être un PDF valide.');
    }
    $content = (string) file_get_contents($file['tmp_name']);
    if (preg_match('#/(JavaScript|Launch)\b#', $content)) {
        throw new RuntimeException('Ce PDF contient du code exécutable (JavaScript ou action de lancement) et a été refusé par sécurité. Ré-exportez-le depuis Word ou LibreOffice.');
    }
    $dir = STORAGE . '/docs';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $name = bin2hex(random_bytes(12)) . '.pdf';
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Impossible d\'enregistrer le PDF.');
    }
    return ['file' => $name, 'size' => (int) $file['size']];
}

function doc_path(string $name): ?string
{
    if (!preg_match('/^[a-f0-9]{24}\.pdf$/', $name)) {
        return null;
    }
    $path = STORAGE . '/docs/' . $name;
    return is_file($path) ? $path : null;
}

function delete_doc(?string $name): void
{
    if ($name && ($path = doc_path($name))) {
        @unlink($path);
    }
}

function send_pdf(string $path, string $downloadName): never
{
    $safe = slugify(pathinfo($downloadName, PATHINFO_FILENAME)) . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: inline; filename="' . $safe . '"');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    header('Cache-Control: private, max-age=3600');
    readfile($path);
    exit;
}
