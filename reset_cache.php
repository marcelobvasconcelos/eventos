<?php
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPcache resetado com sucesso.";
    } else {
        echo "Falha ao resetar OPcache.";
    }
} else {
    echo "OPcache não está habilitado ou a função não existe.";
}
?>
