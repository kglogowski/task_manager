<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Data\DatabaseBundle\Entity\UzytkownikProjekt;
use Data\DatabaseBundle\Entity\Grupa;

/**
 * Uzytkownik
 *
 * @ORM\Table(name="uzytkownicy")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\UzytkownikRepository")
 */
class Uzytkownik implements AdvancedUserInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="imie", type="string", length=40)
     */
    private $imie;

    /**
     * @var string
     *
     * @ORM\Column(name="nazwisko", type="string", length=40)
     */
    private $nazwisko;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=40)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=40)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="haslo", type="string", length=80)
     */
    private $haslo;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=80)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=80, nullable=true)
     */
    private $token;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active_token", type="boolean", nullable=true, options={"default" = "0"})
     */
    private $active_token;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true, options={"default" = "0"})
     */
    private $is_active;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var datetime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     *
     * @var integer
     * @ORM\Column(name="count_login", type="integer", nullable=true, options={"default" = 0})
     * 
     */
    private $count_login;

    /**
     * @var datetime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $last_login;

    /**
     * @var datetime
     *
     * @ORM\Column(name="penultimate_login", type="datetime", nullable=true)
     */
    private $penultimate_login;

    /**
     * @var roles
     * @ORM\ManyToMany(targetEntity="Role")
     */
    private $roles;

    /**
     * @ORM\ManyToMany(targetEntity="Task", mappedBy="uzytkownicy")
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity="UzytkownikProjekt", mappedBy="uzytkownik")
     */
    private $uzytkownicy_projekty;

    /**
     * @ORM\OneToMany(targetEntity="Wiadomosc", mappedBy="uzytkownik")
     */
    private $wiadomosci;

    /**
     * @var Grupa
     *
     * @ORM\ManyToOne(targetEntity="Grupa",inversedBy="uzytkownicy")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $grupa;

    public function __construct() {
        $this->wiadomosci = new ArrayCollection();
        $this->uzytkownicy_projekty = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->salt = uniqid() . uniqid();
        $this->created_at = new \DateTime('now');
    }

    public function __sleep() {
        return array('id');
    }

    /**
     * Get uzytkownik_id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set imie
     *
     * @param string $imie
     * @return Uzytkownik
     */
    public function setImie($imie) {
        $this->imie = $imie;

        return $this;
    }

    /**
     * Get imie
     *
     * @return string 
     */
    public function getImie() {
        return $this->imie;
    }

    /**
     * Set nazwisko
     *
     * @param string $nazwisko
     * @return Uzytkownik
     */
    public function setNazwisko($nazwisko) {
        $this->nazwisko = $nazwisko;

        return $this;
    }

    /**
     * Get nazwisko
     *
     * @return string 
     */
    public function getNazwisko() {
        return $this->nazwisko;
    }

    /**
     * Set login
     *
     * @param string $login
     * @return Uzytkownik
     */
    public function setLogin($login) {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * Set haslo
     *
     * @param string $haslo
     * @return Uzytkownik
     */
    public function setHaslo($haslo, $factory = null) {
        if ($factory == null) {
            return $this;
        }
        $encoder = $factory->getEncoder($this);
        $this->haslo = $encoder->encodePassword($haslo, $this->getSalt());
        return $this;
    }

    /**
     * Get haslo
     *
     * @return string 
     */
    public function getHaslo() {
        return $this->haslo;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function getToken() {
        return $this->token;
    }

    public function getIsActive() {
        return $this->is_active;
    }

    public function setIsActive($is_active = false) {
        $this->is_active = $is_active;
        return $this;
    }

    public function getRoles() {
        $arrRole = array();
        foreach ($this->roles as $role) {
            $arrRole[] = $role->getNazwa();
        }
        return $arrRole;
    }

    public function getRolesCollection() {
        return $this->roles;
    }

    public function addRole(Role $role) {
        $this->roles->add($role);
    }

    public function isGranted($role) {
        return in_array($role, $this->getRoles());
    }

    public function removeRole(Role $role) {
        $this->roles->removeElement($role);
    }

    public function generateToken($m) {
        $_token = FALSE;
        $token = \md5(time());
        while ($_token == FALSE) {
            if (!$m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneByToken($token)) {
                $_token = $token;
            } else {
                $token = md5(time());
            }
        }
//        echo $_token; die;
        $this->token = $_token;
        return $this;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function setUpdatedAt() {
        $this->updated_at = new \DateTime('now');
    }

    public function getCountLogin() {
        return $this->count_login;
    }

    public function setCountLogin() {
        $this->count_login++;
    }

    public function getLastLoginFormatted() {
        return date_format($this->last_login, 'Y-m-d H:i:s');
    }

    public function getLastLogin() {
        return $this->last_login;
    }

    public function setLastLogin() {
        $this->penultimate_login = $this->last_login;
        $this->last_login = new \DateTime('now');
    }

    public function getPenultimateLoginFormatted() {
        return $this->penultimate_login == null ? 'brak' : date_format($this->penultimate_login, 'Y-m-d H:i:s');
    }

    public function eraseCredentials() {
        
    }

    public function getPassword() {
        return $this->haslo;
    }

    public function getUsername() {
        return $this->login;
    }

    public function isAccountNonExpired() {
        return true;
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isEnabled() {
        return true;
    }

    public function getAuthenticationToken() {
        return new UsernamePasswordToken($this, 'brak', 'secured_area', $this->getRoles());
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Uzytkownik
     */
    public function setSalt($salt) {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Uzytkownik
     */
    public function setCreatedAt($createdAt) {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Set penultimate_login
     *
     * @param \DateTime $penultimateLogin
     * @return Uzytkownik
     */
    public function setPenultimateLogin($penultimateLogin) {
        $this->penultimate_login = $penultimateLogin;

        return $this;
    }

    /**
     * Get penultimate_login
     *
     * @return \DateTime 
     */
    public function getPenultimateLogin() {
        return $this->penultimate_login;
    }

    /**
     * Add uzytkownicy_projekty
     *
     * @param \Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty
     * @return Uzytkownik
     */
    public function addUzytkownicyProjekty(\Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty) {
        $this->uzytkownicy_projekty[] = $uzytkownicyProjekty;

        return $this;
    }

    /**
     * Remove uzytkownicy_projekty
     *
     * @param \Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty
     */
    public function removeUzytkownicyProjekty(\Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty) {
        $this->uzytkownicy_projekty->removeElement($uzytkownicyProjekty);
    }

    /**
     * Get uzytkownicy_projekty
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUzytkownicyProjekty() {
        return $this->uzytkownicy_projekty;
    }

    public function getTasks() {
        return $this->tasks->toArray();
    }

    public function addTask(Task $tasks) {
        $this->tasks->add($tasks);
    }

    public function removeTask(Task $tasks) {
        $this->tasks->removeElement($tasks);
    }

    public function getRoleProjektuByProjektId($projektId) {
        $collUp = $this->getUzytkownicyProjekty();
        foreach ($collUp as $up) {
            if ($up->getProjekt()->getId() == $projektId) {
                return $up->getRola();
            }
        }
        return null;
    }

    public function setRoleProjektuByProjektId($projektId, $rola) {
        $collUp = $this->getUzytkownicyProjekty();
        foreach ($collUp as $up) {
            if ($up->getProjekt()->getId() == $projektId) {
                $up->setRola($rola);
                return $this;
            }
        }
    }

    public function isAssignedToTask(Task $task) {
        return in_array($task, $this->getTasks());
    }

    public function isActiveToken() {
        return $this->active_token ? TRUE : FALSE;
    }

    public function setActiveToken($active_token) {
        $this->active_token = $active_token;
    }

    public function getGrupa() {
        return $this->grupa;
    }

    public function setGrupa(Grupa $grupa) {
        $this->grupa = $grupa;
        return $this;
    }

    public function hasUprawnienie($numer) {
        $grupa = $this->getGrupa();
        if ($grupa) {
            $uprawnienia = $grupa->getUprawnieniaArray();
            foreach ($uprawnienia as $uprawnienie) {
                /* @var $uprawnienie Uprawnienie */
                if ($uprawnienie->getNumber() == $numer) {
                    return true;
                }
            }
        }
        return false;
    }

}
