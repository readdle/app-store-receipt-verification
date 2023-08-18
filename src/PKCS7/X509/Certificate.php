<?php
declare(strict_types=1);

namespace Readdle\AppStoreReceiptVerification\PKCS7\X509;

use Readdle\AppStoreReceiptVerification\ASN1\AbstractASN1Object;
use Readdle\AppStoreReceiptVerification\PKCS7\AbstractPKCS7Object;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\Extension;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\Extensions;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\Name;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\SubjectPublicKeyInfo;
use Readdle\AppStoreReceiptVerification\PKCS7\X501\Validity;

final class Certificate extends AbstractPKCS7Object
{
    private string $version;
    private string $serialNumber;
    private AlgorithmIdentifier $signature;
    private Name $issuer;
    private Validity $validity;
    private Name $subject;
    private SubjectPublicKeyInfo $subjectPublicKeyInfo;
    private Extensions $extensions;

    protected function __construct(
        string $version,
        string $serialNumber,
        AlgorithmIdentifier $signature,
        Name $issuer,
        Validity $validity,
        Name $subject,
        SubjectPublicKeyInfo $subjectPublicKeyInfo,
        Extensions $extensions
    ) {
        $this->version = $version;
        $this->serialNumber = $serialNumber;
        $this->signature = $signature;
        $this->issuer = $issuer;
        $this->validity = $validity;
        $this->subject = $subject;
        $this->subjectPublicKeyInfo = $subjectPublicKeyInfo;
        $this->extensions = $extensions;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getSignature(): AlgorithmIdentifier
    {
        return $this->signature;
    }

    public function getIssuer(): Name
    {
        return $this->issuer;
    }

    public function getValidity(): Validity
    {
        return $this->validity;
    }

    public function getSubject(): Name
    {
        return $this->subject;
    }

    public function getSubjectPublicKeyInfo(): SubjectPublicKeyInfo
    {
        return $this->subjectPublicKeyInfo;
    }

    /**
     * @return array<Extension>
     */
    public function getExtensions(): array
    {
        return $this->extensions->getExtensions();
    }

    public static function fromASN1Object(AbstractASN1Object $object): self
    {
        self::assertASNIdentifierType($object, 'Sequence');
        self::assertASNChildrenStruct($object, [
            'Constructed Context-Specific 0x0',
            'Integer',
            'Sequence',
            'Sequence',
            'Sequence',
            'Sequence',
            'Sequence',
            'Constructed Context-Specific 0x3',
        ]);

        [
            $version,
            $serialNumber,
            $signature,
            $issuer,
            $validity,
            $subject,
            $subjectPublicKeyInfo,
            $extensions
        ] = $object->getValue();

        return new self(
            $version->getValue()[0]->getValue(),
            $serialNumber->getValue(),
            AlgorithmIdentifier::fromASN1Object($signature),
            Name::fromASN1Object($issuer),
            Validity::fromASN1Object($validity),
            Name::fromASN1Object($subject),
            SubjectPublicKeyInfo::fromASN1Object($subjectPublicKeyInfo),
            Extensions::fromASN1Object($extensions->getValue()[0])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'serialNumber' => $this->getSerialNumber(),
            'signature' => $this->getSignature(),
            'issuer' => $this->getIssuer(),
            'validity' => $this->getValidity(),
            'subject' => $this->getSubject(),
            'subjectPublicKeyInfo' => $this->getSubjectPublicKeyInfo(),
            'extensions' => $this->getExtensions(),
        ];
    }
}
