<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use DateTime;
use DateTimeZone;
use Exception;
use Readdle\AppStoreReceiptVerification\PKCS7\AppStore\AbstractField;

use function base64_encode;
use function chunk_split;
use function dechex;
use function is_numeric;
use function join;
use function ord;
use function str_pad;
use function strlen;
use function strtoupper;
use function trim;

final class Utils
{
    public static function formatHexString(string $string): string
    {
        $hex = [];

        for ($i = 0; $i < strlen($string); $i++) {
            $hex[] = str_pad(strtoupper(dechex(ord($string[$i]))), 2, '0', STR_PAD_LEFT);
        }

        return join(' ', $hex);
    }

    /**
     * @param array<AbstractField> $fields
     * @return array<string, mixed>
     */
    public static function receiptJsonSerialize(array $fields, bool $includeUnknown = false): array
    {
        $receipt = [];
        $unknown = [];

        foreach ($fields as $field) {
            $type = $field->getTypeString();
            $value = $field->isBinary() ? Utils::formatHexString($field->getValue()) : $field->getValue();

            if (is_numeric($type)) {
                if ($includeUnknown) {
                    $unknown[$type] = $value;
                }
            } elseif ($field->isDateTime()) {
                if (empty($value)) {
                    continue;
                }

                try {
                    $dt = new DateTime($value);
                } catch (Exception $e) {
                    continue;
                }

                $receipt[$type . '_ms'] = (string) ($dt->getTimestamp() * 1000);

                $dt->setTimezone(new DateTimeZone('Etc/GMT'));
                $receipt[$type] = $dt->format('Y-m-d H:i:s e');

                $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
                $receipt[$type . '_pst'] = $dt->format('Y-m-d H:i:s e');
            } elseif (in_array($type, ['is_trial_period', 'is_in_intro_offer_period'])) {
                $receipt[$type] = $value ? 'true' : 'false';
            } else {
                $receipt[$type] = $value;
            }
        }

        if (!empty($unknown)) {
            $receipt['unknown'] = $unknown;
        }

        return $receipt;
    }

    public static function DER2PEM(string $der): string
    {
        return join("\n", [
            '-----BEGIN CERTIFICATE-----',
            trim(chunk_split(base64_encode($der), 64)),
            '-----END CERTIFICATE-----',
        ]);
    }

    public static function stringifyOID(string $oid): string
    {
        $string = ObjectIdentifierTree::toString($oid);

        if (!$string) {
            return $oid;
        }

        return $string . " ($oid)";
    }
}
