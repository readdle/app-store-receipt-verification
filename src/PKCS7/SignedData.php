<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X509\AlgorithmIdentifier;

final class SignedData extends AbstractData
{
    private string $version;
    private DigestAlgorithmIdentifiers $digestAlgorithms;
    private ContentInfo $contentInfo;
    private CertificateSet $certificateSet;
    private SignerInfos $signerInfos;

    protected function __construct(
        string $version,
        DigestAlgorithmIdentifiers $digestAlgorithms,
        ContentInfo $contentInfo,
        CertificateSet $certificateSet,
        SignerInfos $signerInfos
    ) {
        $this->version = $version;
        $this->digestAlgorithms = $digestAlgorithms;
        $this->contentInfo = $contentInfo;
        $this->certificateSet = $certificateSet;
        $this->signerInfos = $signerInfos;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array<AlgorithmIdentifier>
     */
    public function getDigestAlgorithms(): array
    {
        return $this->digestAlgorithms->getAlgorithms();
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getCertificateSet(): CertificateSet
    {
        return $this->certificateSet;
    }

    public function getSignerInfos(): SignerInfos
    {
        return $this->signerInfos;
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'Integer',
            'Set',
            'Sequence',
            'Constructed Context-Specific 0x0',
            'Set',
        ]);

        [$version, $digestAlgorithms, $contentInfo, $certificates, $signerInfos] = $object->getValue();
        return new self(
            $version->getValue(),
            DigestAlgorithmIdentifiers::fromASN1Object($digestAlgorithms),
            ContentInfo::fromASN1Object($contentInfo),
            CertificateSet::fromASN1Object($certificates),
            SignerInfos::fromASN1Object($signerInfos)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'digestAlgorithms' => $this->getDigestAlgorithms(),
            'contentInfo' => $this->getContentInfo(),
            'certificates' => $this->getCertificateSet(),
            'signerInfos' => $this->getSignerInfos(),
        ];
    }
}
