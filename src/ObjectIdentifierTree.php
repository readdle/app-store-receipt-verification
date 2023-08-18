<?php
/** @noinspection SpellCheckingInspection */
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification;

use function explode;

abstract class ObjectIdentifierTree
{
    const PKCS7__DATA = '1.2.840.113549.1.7.1';

    const PKCS7__SIGNED_DATA = '1.2.840.113549.1.7.2';

    const SIGNATURE__SHA1 = '1.3.14.3.2.26';

    const SIGNATURE__SHA256 = '2.16.840.1.101.3.4.2.1';

    const TREE = [
        1 => [
            'name' => 'iso',

            2 => [
                'name' => 'member-body',

                840 => [
                    'name' => 'us',

                    113549 => [
                        'name' => 'rsadsi',

                        1 => [
                            'name' => 'pkcs',

                            1 => [
                                'name' => 'pkcs-1',

                                1 => [
                                    'name' => 'rsaEncryption',
                                ],

                                5 => [
                                    'name' => 'sha1-with-rsa-signature',
                                ],

                                11 => [
                                    'name' => 'sha256WithRSAEncryption',
                                ],
                            ],

                            7 => [
                                'name' => 'pkcs-7',

                                1 => [
                                    'name' => 'data',
                                ],

                                2 => [
                                    'name' => 'signedData',
                                ],
                            ],
                        ],
                    ],

                    113635 => [
                        'name' => 'apple',

                        100 => [
                            'name' => 'appleDataSecurity',

                            5 => [
                                'name' => 'appleCertificatePolicies',
                            ],

                            6 => [
                                'name' => 'appleCertificateExtensions',
                            ],
                        ],
                    ],
                ],
            ],

            3 => [
                'name' => 'identified-organization',

                6 => [
                    'name' => 'dod',

                    1 => [
                        'name' => 'internet',

                        5 => [
                            'name' => 'security',

                            5 => [
                                'name' => 'mechanisms',

                                7 => [
                                    'name' => 'pkix',

                                    1 => [
                                        'name' => 'pe',

                                        1 => [
                                            'name' => 'authorityInfoAccess',
                                        ],
                                    ],

                                    2 => [
                                        'name' => 'qt',

                                        1 => [
                                            'name' => 'cps',
                                        ],

                                        2 => [
                                            'name' => 'unotice'
                                        ],
                                    ],

                                    48 => [
                                        'name' => 'ad',

                                        1 => [
                                            'name' => 'ocsp',
                                        ],

                                        2 => [
                                            'name' => 'caIssuers',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                14 => [
                    'name' => 'oiw',

                    3 => [
                        'name' => 'secsig',

                        2 => [
                            'name' => 'algorithms',

                            26 => [
                                'name' => 'hashAlgorithmIdentifier',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        2 => [
            'name' => 'joint-iso-itu-t',

            5 => [
                'name' => 'ds',

                4 => [
                    'name' => 'attributeType',

                    3 => [
                        'name' => 'commonName',
                    ],

                    6 => [
                        'name' => 'countryName',
                    ],

                    10 => [
                        'name' => 'organizationName',
                    ],

                    11 => [
                        'name' => 'organizationUnitName',
                    ],
                ],

                29 => [
                    'name' => 'certificateExtension',

                    14 => [
                        'name' => 'subjectKeyIdentifier',
                    ],

                    15 => [
                        'name' => 'keyUsage',
                    ],

                    19 => [
                        'name' => 'basicConstraints',
                    ],

                    31 => [
                        'name' => 'cRLDistributionPoints',
                    ],

                    32 => [
                        'name' => 'certificatePolicies',
                    ],

                    35 => [
                        'name' => 'authorityKeyIdentifier',
                    ],
                ],
            ],

            16 => [
                'name' => 'country',

                840 => [
                    'name' => 'us',

                    1 => [
                        'name' => 'organization',

                        101 => [
                            'name' => 'gov',

                            3 => [
                                'name' => 'csor',

                                4 => [
                                    'name' => 'nistAlgorithm',

                                    2 => [
                                        'name' => 'hashAlgs',

                                        1 => [
                                            'name' => 'sha256',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    public static function toString(string $numeric): ?string
    {
        $pointer = self::TREE;

        foreach (explode('.', $numeric) as $entry) {
            if (empty($pointer[$entry])) {
                return null;
            }

            $pointer = $pointer[$entry];
        }

        return $pointer['name'] ?? null;
    }
}
