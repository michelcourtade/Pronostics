<?php

namespace Dwf\PronosticsBundle\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider extends FOSUBUserProvider
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //on connect - get the access token and the user ID
        $service      = $response->getResourceOwner()->getName();
        $setter       = 'set'.ucfirst($service);
        $setter_id    = $setter.'Id';
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
        $email    = $response->getEmail();

        $user = $this->userManager->findUserBy(array('email' => $email));
        //when the user is registrating
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';

            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());

            $user->setUsername($nickname);
            $user->setEmail($username);
            if($email) {
                $user->setEmail($email);
            }

            if ($response->getProfilePicture()) {
                $img  = 'uploads/documents/'.$username.'.jpg';
                $dest = dirname(__FILE__). '/../../../../web/'.$img;
                copy($response->getProfilePicture(), $dest);
                $user->setProfilePicture($img);
            }
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

        if ($response->getProfilePicture()) {
            $img  = 'uploads/documents/'.$username.'.jpg';
            $dest = dirname(__FILE__). '/../../../../web/'.$img;
            copy($response->getProfilePicture(), $dest);
            $user->setProfilePicture($img);
        }

        $this->userManager->updateUser($user);

        $user = parent::loadUserByOAuthUserResponse($response);

        $setterToken = 'set' . ucfirst($serviceName) . 'AccessToken';
        $user->$setterToken($response->getAccessToken());

        return $user;
    }
}