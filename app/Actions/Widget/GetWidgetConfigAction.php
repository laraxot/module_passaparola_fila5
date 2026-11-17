<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Widget;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Passaparola\Models\ChatWidget;
use Spatie\QueueableAction\QueueableAction;

/**
 * Testi del widget nella lingua chiesta: saluto dell'attivita', avviso che risponde un'AI
 * (obbligo di trasparenza), etichette dell'interfaccia.
 *
 * Rotta: GET /api/passaparola/widgets/{publicKey}/config?lang=it
 */
final class GetWidgetConfigAction
{
    use QueueableAction;

    public const array SUPPORTED_LOCALES = ['it', 'en'];

    public function __construct(private readonly ResolveChatWidgetAction $resolveWidget) {}

    public function __invoke(Request $request, string $publicKey): JsonResponse
    {
        $widget = $this->resolveWidget->execute($publicKey, $request->headers->get('Origin'));
        $lang = $request->query('lang');

        return response()->json($this->execute($widget, is_string($lang) ? $lang : null));
    }

    /** @return array<string, string|int> */
    public function execute(ChatWidget $widget, ?string $lang): array
    {
        $locale = in_array($lang, self::SUPPORTED_LOCALES, true) ? $lang : self::SUPPORTED_LOCALES[0];
        $t = static fn (string $key): string => \trans_string('passaparola::widget.'.$key, [], $locale);

        return [
            'name' => $widget->workspace?->name ?? $widget->name,
            'greeting' => $widget->greeting ?? $t('default_greeting'),
            'ai_notice' => $t('ai_notice'),
            'placeholder' => $t('placeholder'),
            'send' => $t('send'),
            'open' => $t('open'),
            'close' => $t('close'),
            'error' => $t('error'),
            'max_length' => SendVisitorMessageAction::MAX_LENGTH,
        ];
    }
}
