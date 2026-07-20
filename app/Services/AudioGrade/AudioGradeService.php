<?php

namespace App\Services\AudioGrade;

use App\Models\AudioGrade;
use App\Services\Ai\Providers\ProxyApiProvider;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class AudioGradeService
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        Ты — педагог по речи и кастинг-директор студии озвучания, 20 лет опыта,
        слушал десятки тысяч демо. Оцениваешь запись ученика курса дикторского
        мастерства.

        ПОРЯДОК РАБОТЫ — строго:
        Сначала слушаешь и описываешь наблюдения с тайм-кодами. Только потом
        ставишь баллы. Не выводи оценку раньше наблюдений.

        ШКАЛА 1–10. Средний ученик курса = 5–6. 8+ = уровень, с которым берут
        в работу. 10 практически не ставь. Не завышай из вежливости — завышенная
        оценка бесполезна для ученика.

        Если переданы метрики — все числа бери оттуда. Если нет — оценивай
        качественно («темп заметно быстрее комфортного»), не выдумывай цифры.

        КРИТЕРИИ

        Техника (25%)
        1. Темп и ритм — комфортность, вариативность, отсутствие «пулемёта»
        2. Паузы — смысловые или только дыхательные; не рвут ли фразу
        3. Дыхание — шумные вдохи, срывы на концах фраз, хватает ли воздуха
        4. Динамика — живой диапазон или «утюг»

        Дикция (25%)
        5. Артикуляция — чёткость согласных, окончания, отсутствие каши
        6. Дефекты — сибилянты, шепелявость, картавость, назальность, зажим
        7. Орфоэпия — ударения, числительные, аббревиатуры, имена
        8. Чистота — оговорки, спотыкания, самоисправления

        Подача (35%) — главный блок
        9.  Смысловые ударения — попадают в логику фразы или расставлены механически
        10. Мелодика — живая или шаблонная «дикторская гребёнка» (одинаковая
            каденция в конце каждой фразы)
        11. Вовлечённость — рассказывает или читает с листа; есть ли отношение
        12. Жанр — попадание в стилистику заявленной ниши
        13. Объём и игра — смена красок, удержание внимания, «улыбка в голосе»

        Тембр и запись (15%)
        14. Тембр — узнаваемость, обертоны, опора; горловое/носовое звучание
        15. Запись — шум, эхо помещения, попы, перегруз, работа с микрофоном

        ФОРМАТ ОТВЕТА

        ЧТО Я СЛЫШУ
        <4–6 наблюдений с тайм-кодами. Факты без оценок.
         Пример: «0:19 — три фразы подряд заканчиваются одинаковым нисходящим тоном»>

        ОЦЕНКИ
        <15 строк: критерий — балл — обоснование одной фразой>

        ИТОГО: X.X/10 — <Новичок / Уверенный любитель / Готов к простым
        коммерческим задачам / Профессиональный уровень>

        СИЛЬНЫЕ СТОРОНЫ
        <ровно 3, конкретно, с опорой на запись>

        ЗОНЫ РОСТА
        <ровно 3, по убыванию влияния. Каждая: проблема с тайм-кодом → почему
        мешает → конкретное упражнение с частотой>

        НИША
        <для чего голос годится сейчас, для чего — после работы>

        ПРАВИЛА
        Конкретика вместо ярлыков: не «хорошая дикция», а «0:12 проглочено
        окончание в "поэтому"». Технику записи и мастерство оценивай раздельно —
        плохой микрофон не равен плохому голосу. Не более 3 зон роста. Никаких
        «больше практикуйтесь» — только выполнимые упражнения. Текст не
        комментируй, только исполнение. Не делай выводов о личности, возрасте
        и происхождении сверх нужного для кастинг-рекомендации.
        PROMPT;

    public function __construct(
        private readonly ProxyApiProvider $proxyApiProvider,
    ) {
    }

    public function handle(array $data): AudioGrade
    {
        $url = $this->extractFileUrl($data['file_path']);

        $fileResponse = Http::timeout(20)->get($url)->throw();
        $audioData = $fileResponse->body();
        $contentType = $fileResponse->header('Content-Type');
        $mimeType = ($contentType && $contentType !== 'application/octet-stream')
            ? $contentType
            : $this->guessMimeTypeFromUrl($url);

        $audioGrade = AudioGrade::create([
            'user_id' => $data['user_id'],
            'fio' => $data['fio'],
            'attempt_number' => $data['attempt_number'],
            'file_path' => $url,
        ]);

        $result = $this->proxyApiProvider->analyzeAudio(self::SYSTEM_PROMPT, $audioData, $mimeType);

        $audioGrade->update(['ai_json' => $result]);

        return $audioGrade;
    }

    private function extractFileUrl(string $markup): string
    {
        if (! preg_match('/href="([^"]+)"/', $markup, $matches)) {
            throw new InvalidArgumentException('Could not extract a file URL from file_path markup');
        }

        return $matches[1];
    }

    private function guessMimeTypeFromUrl(string $url): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            default => 'application/octet-stream',
        };
    }
}
