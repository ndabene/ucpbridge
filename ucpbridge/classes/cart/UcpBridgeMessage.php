<?php
/**
 * Messages UCP renvoyés aux agents et enveloppes des réponses (logique pure). Le code (`code`) est stable et sert aux
 * agents ; le texte (`content`) est lisible par un humain.
 *
 * Copyright (c) 2026 Friends of Presta. Auteur : Christophe Vidal, pour le podcast Contrib. Licence AFL-3.0 (Academic Free License 3.0), voir LICENSE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class UcpBridgeMessage
{
    /**
     * Construit un message d'erreur UCP avec chemin JSON facultatif et champs supplémentaires.
     *
     * @param string $code Code stable pour les agents.
     * @param string $content Texte lisible par un humain.
     * @param string $severity Gravité UCP du message.
     * @param string|null $path Chemin JSON du champ en erreur.
     * @param array<string, mixed> $extra Autres champs UCP du message.
     * @return array<string, mixed> Message d'erreur UCP.
     */
    public static function error($code, $content, $severity = 'recoverable', $path = null, array $extra = [])
    {
        $m = ['type' => 'error', 'code' => $code, 'content' => $content, 'severity' => $severity];
        if ($path) {
            $m['path'] = $path;
        }

        return $m + $extra;
    }

    /** @return array{type: string, code: string, content: string} Message informatif UCP. */
    public static function info($code, $content)
    {
        return ['type' => 'info', 'code' => $code, 'content' => $content];
    }

    /**
     * Capacités annoncées dans l'enveloppe `ucp`, toutes à la version du module.
     *
     * @param list<string> $names Noms des capacités UCP.
     * @param string $version Version annoncée pour chaque capacité.
     * @return array<string, list<array{version: string}>> Capacités indexées par nom.
     */
    public static function capabilities(array $names, $version)
    {
        $caps = [];
        foreach ($names as $c) {
            $caps[$c] = [['version' => $version]];
        }

        return $caps;
    }

    /**
     * Enveloppe `ucp` d'une réponse réussie ; gestionnaires de paiement ajoutés pour la caisse.
     *
     * @param list<string> $capabilities Noms des capacités UCP.
     * @param string $version Version de l'API UCP.
     * @param array<string, mixed>|null $paymentHandlers Gestionnaires publiés pour la caisse.
     * @return array<string, mixed> Enveloppe UCP de succès.
     */
    public static function envelope(array $capabilities, $version, ?array $paymentHandlers = null)
    {
        $e = ['version' => $version, 'status' => 'success', 'capabilities' => self::capabilities($capabilities, $version)];
        if (null !== $paymentHandlers) {
            $e['payment_handlers'] = (object) $paymentHandlers;
        }

        return $e;
    }

    /**
     * Document d'erreur UCP : enveloppe en statut error, messages, lien de retour vers la boutique.
     *
     * @param list<array<string, mixed>> $messages Messages d'erreur UCP.
     * @param list<string> $capabilities Noms des capacités UCP.
     * @param string $version Version de l'API UCP.
     * @param string $continueUrl Lien de retour vers la boutique.
     * @return array<string, mixed> Document d'erreur UCP.
     */
    public static function errorDoc(array $messages, array $capabilities, $version, $continueUrl)
    {
        return [
            'ucp' => ['version' => $version, 'status' => 'error', 'capabilities' => self::capabilities($capabilities, $version)],
            'messages' => $messages,
            'continue_url' => $continueUrl,
        ];
    }
}
