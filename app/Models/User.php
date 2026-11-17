<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models;

use Modules\User\Models\BaseUser;

/**
 * Utente del progetto Passaparola (titolari, operatori, amministratori).
 *
 * Tutta la logica sta in BaseUser (modulo User, connessione `user`): qui solo il tipo
 * concreto usato da auth, morph map e commenti nella config del tenant.
 */
class User extends BaseUser {}
