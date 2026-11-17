<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Widget;

use Modules\AI\Actions\CreateAiThreadAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Passaparola\Models\Conversation;
use Spatie\QueueableAction\QueueableAction;

/**
 * Riprende la conversazione del visitatore (stesso widget, non chiusa) o ne apre una nuova
 * con il suo thread AI. Un token di un altro widget o di una conversazione chiusa non riapre nulla.
 */
final class FindOrStartConversationAction
{
    use QueueableAction;

    public const string PANEL_ID = 'passaparola-widget';

    public function __construct(private readonly CreateAiThreadAction $createThread) {}

    public function execute(ChatWidget $widget, ?string $visitorToken): Conversation
    {
        if ($visitorToken !== null && $visitorToken !== '') {
            $existing = Conversation::query()
                ->where('chat_widget_id', $widget->id)
                ->where('visitor_token', $visitorToken)
                ->first();

            if ($existing instanceof Conversation && $existing->status->acceptsMessages()) {
                return $existing;
            }
        }

        $thread = $this->createThread->execute(null, self::PANEL_ID, [
            'workspace_id' => $widget->workspace_id,
            'chat_widget_id' => $widget->id,
        ]);

        $conversation = new Conversation;
        $conversation->fill([
            'workspace_id' => $widget->workspace_id,
            'chat_widget_id' => $widget->id,
            'ai_thread_id' => $thread->id,
            'visitor_token' => Conversation::generateVisitorToken(),
            'status' => ConversationStatusEnum::OPEN,
        ]);
        $conversation->save();

        return $conversation;
    }
}
