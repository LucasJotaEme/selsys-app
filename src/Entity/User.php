<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiToken = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Upload $upload = null;

    #[ORM\ManyToMany(targetEntity: UserType::class, mappedBy: 'users')]
    private Collection $userTypes;

    public function __construct()
    {
        $this->userTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUpload(): ?Upload
    {
        return $this->upload;
    }

    public function setUpload(?Upload $upload): self
    {
        $this->upload = $upload;

        return $this;
    }

    /**
     * @return Collection<int, UserType>
     */
    public function getUserTypes(): Collection
    {
        return $this->userTypes;
    }

    public function addUserType(UserType $userType): self
    {
        if (!$this->userTypes->contains($userType)) {
            $this->userTypes->add($userType);
            $userType->addUser($this);
        }

        return $this;
    }

    public function removeUserType(UserType $userType): self
    {
        if ($this->userTypes->removeElement($userType)) {
            $userType->removeUser($this);
        }

        return $this;
    }
}
