# DevPulse, diplomová práce, FIT ČVUT

Text diplomové práce je v samostatném repozitáři: https://github.com/paukert/fit-ctu-masters-thesis/

## Spuštění na lokálním prostředí
1. stáhnout a nainstalovat Docker - https://www.docker.com/get-started/ (Docker Desktop - https://www.docker.com/products/docker-desktop/)
2. naklonovat [DevPulse repozitář](https://github.com/paukert/dev-pulse)
3. vytvořit `.env` soubor a do něj překopírovat obsah souboru `.env.example`
4. sestavit a spustit kontejnery pomocí příkazu `docker-compose up -d`
5. nainstalovat PHP závislosti pomocí balíčkovacího systému Composer `docker-compose exec php php composer install`
6. nainstalovat JavaScriptové závislosti `docker-compose exec php npm install`
7. sestavit Single page aplikaci `docker-compose exec php npm run build`
8. vygenerovat bezpečnostní klíč pro aplikaci `docker-compose exec php php artisan key:generate`
9. spustit databázové migrace `docker-compose exec php php artisan migrate`
10. testovací data lze nagenerovat pomocí `docker-compose exec php php artisan db:seed`

Po provedení výše uvedených kroků bude systém dostupný na adrese http://localhost/.

Všechny kontejnery lze naopak zastavit pomocí `docker-compose down`.

V rámci běhu databázových migrací bude vytvořen uživatel s administrátorskou rolí:
- e-mail: `admin@devpulse.com`
- heslo: `password`

## Poděkování
<img src="https://fit.cvut.cz/static/images/fit-cvut-logo-cs.svg" alt="logo FIT ČVUT" height="100">

Tento software vznikl za podpory **Fakulty informačních technologií ČVUT v Praze**. Více informací naleznete na [fit.cvut.cz](https://fit.cvut.cz).

## Licence
Tato aplikace je open-source software licencovaný pod [MIT licencí](LICENSE).

### Upozornění k použitým knihovnám
Tento projekt využívá knihovnu Highcharts pro vizualizaci dat. Tato knihovna **není** šířena pod MIT licencí a vztahují se na ni vlastní licenční podmínky společnosti Highsoft AS. Pro její využívání je nezbytné si obstarat odpovídající licenci (včetně licencí pro nekomerční/akademické účely) na [shop.highcharts.com](https://shop.highcharts.com/).
