<?php

namespace Dwf\PronosticsBundle\OAuth;

use FOS\UserBundle\Model\UserInterface as FOSUserInterface;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;

use Dwf\PronosticsBundle\Entity\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Loading and ad-hoc creation of a user by an OAuth sign-in provider account.
 *
 * @author Fabian Kiss <fabian.kiss@ymc.ch>
 */
class UserProvider extends FOSUBUserProvider
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        var_dump($response->getProfilePicture());
        exit();
        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        if(!$user->getProfilePicture())
            $user->setProfilePicture($response->getProfilePicture());
        $this->userManager->updateUser($user);
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $nickname = $response->getNickname();
        $email = $response->getEmail();

        //$user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        $user = $this->userManager->findUserBy(array('email' => $email));
        //when the user is registrating
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            //I have set all requested data with the user's username
            //modify here with relevant data
            $user->setUsername($nickname);
            if($email)
                $user->setEmail($email);
            else $user->setEmail($username);
            
            $user->setProfilePicture($response->getProfilePicture());
            if(!$user->getPassword()) {
                // generate unique token
                $secret = md5(uniqid(rand(), true));
                $user->setPassword($secret);
            }
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            return $user;
        }
        //if user exists - go with the HWIOAuth way
        $serviceName = $response->getResourceOwner()->getName();
        $setterId = 'set' . ucfirst($serviceName) . 'Id';
        $user->$setterId($response->getUsername());
        $user->setProfilePicture($response->getProfilePicture());
        $this->userManager->updateUser($user);
        $user = parent::loadUserByOAuthUserResponse($response);
        $setterToken = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setterToken($response->getAccessToken());
        return $user;
    }
}