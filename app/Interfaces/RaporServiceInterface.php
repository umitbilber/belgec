<?php

declare(strict_types=1);

namespace App\Interfaces;

interface RaporServiceInterface
{
    public function aylikFaturaOzeti(string $baslangic, string $bitis): array;
    public function enCokSatilanUrunler(string $baslangic, string $bitis, int $limit): array;
    public function cariAlisSatisOzeti(string $baslangic, string $bitis): array;
    public function enYuksekBakiyeliCariler(int $limit): array;
}