# DevPulse, diplomová práce, FIT ČVUT

Text diplomové práce je v samostatném repozitáři: https://github.com/paukert/fit-ctu-masters-thesis/

## Lokální instalace
1. stáhnout a nainstalovat Docker - https://www.docker.com/get-started/ (Docker Desktop - https://www.docker.com/products/docker-desktop/)
2. naklonovat [DevPulse repozitář](https://github.com/paukert/dev-pulse)
3. vytvořit `.env` soubor a do něj překopírovat obsah souboru `.env.example`
4. sestavit a spustit kontejnery pomocí příkazu `docker-compose up -d`
5. nainstalovat PHP závislosti pomocí balíčkovacího systému Composer `docker-compose exec php php composer install`
6. nainstalovat JavaScriptové závislosti `docker-compose exec php npm install`
7. sestavit Single page aplikaci `docker-compose exec php npm run build`
8. vygenerovat bezpečnostní klíč pro aplikaci `docker-compose exec php php artisan key:generate`
9. spustit databázové migrace `docker-compose exec php php artisan migrate`

Po provedení výše uvedených kroků bude systém dostupný na adrese http://localhost/.

Všechny kontejnery lze naopak zastavit pomocí `docker-compose down`.
