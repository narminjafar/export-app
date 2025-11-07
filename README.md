# Laravel Excel → XML API (Docker + JWT)

Bu layihə Docker üzərində qurulmuş Laravel tətbiqidir. Əsas funksiyaları:

1. Excel fayllarını oxumaq (`maatwebsite/excel` vasitəsilə).
2. Oxunan dataya əsaslanaraq XML faylları yaratmaq.
3. API endpointlərini JWT (`tymon/jwt-auth`) ilə qorumaq.

---

## Layihəni qaldırmaq

```bash
# 1. Docker konteynerlərini yarat və qaldır
docker-compose up -d --build

# 2. Laravel üçün dependensiyaları quraşdır
docker exec -it export-api-app composer install

# 3. .env faylını yarad
docker exec -it export-api-app cp .env.example .env

# 4. Laravel application key yarat
docker exec -it export-api-app php artisan key:generate

# 5. Verilənlər bazasını migrate et
docker exec -it export-api-app php artisan migrate

# 6. JWT secret yarad
docker exec -it export-api-app php artisan jwt:secret
