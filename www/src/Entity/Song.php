<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SongRepository;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: SongRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['song:read']],
)]

#[ApiFilter(
    BooleanFilter::class, properties:[
        'album.isActive'
    ]
)]

class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['album:read', 'artist:read', 'user:read', 'playlist:read', 'song:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['album:read', 'artist:read', 'user:read', 'playlist:read', 'song:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['album:read', 'artist:read', 'user:read', 'playlist:read', 'song:read'])]
    private ?string $filePath = null;

    #[Vich\UploadableField(mapping: 'songs', fileNameProperty: 'filePath')]
    private ?File $filePathFile = null;


    #[ORM\Column]
    #[Groups(['album:read', 'artist:read', 'user:read', 'playlist:read', 'song:read'])]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['playlist:read', 'song:read'])]
    private ?Album $album = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = 'upload_' . uniqid() . '_' . $filePath;

        return $this;
    }

    //ici méthode de $filePathFile
    public function getFilePathFile(): ?File
    {
        return $this->filePathFile;
    }

    public function setFilePathFile(?File $filePathFile): void
    {
        $this->filePathFile = $filePathFile;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }
}
