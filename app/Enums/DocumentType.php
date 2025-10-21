<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case SOP = 'sop';
    case WorkInstruction = 'work_instruction';
    case Form = 'form';
    case JobDescription = 'job_description';
    case InternalMemo = 'internal_memo';
    case IncomingLetter = 'incoming_letter';
    case OutgoingLetter = 'outgoing_letter';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SOP => 'SOP',
            self::WorkInstruction => 'Work Instruction',
            self::Form => 'Form',
            self::JobDescription => 'Job Description',
            self::InternalMemo => 'Internal Memo',
            self::IncomingLetter => 'Incoming Letter',
            self::OutgoingLetter => 'Outgoing Letter',
            self::Other => 'Other',
        };
    }

    public function canHaveVersions(): bool
    {
        return match ($this) {
            self::IncomingLetter, self::Other => false,
            default => true,
        };
    }

    public function requiresAccessRequest(): bool
    {
        return match ($this) {
            self::SOP, self::WorkInstruction, self::JobDescription, self::IncomingLetter, self::Other => true,
            default => false,
        };
    }

    public function isTemplate(): bool
    {
        return match ($this) {
            self::InternalMemo, self::OutgoingLetter => true,
            default => false,
        };
    }
}
