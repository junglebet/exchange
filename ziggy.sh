php8.1 artisan ziggy:generate resources/js/ziggy.js && php8.1 artisan ziggy:generate resources/js/ziggy_alt.js && sed -i "s/$1/m.$1/g" resources/js/ziggy_alt.js
