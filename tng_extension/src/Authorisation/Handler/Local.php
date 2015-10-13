<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\TokenManager;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\Bolt\ClientLogin\Exception\InvalidAuthorisationRequestException;
use Bolt\Extension\Bolt\ClientLogin\FormFields;
use Bolt\Extension\Bolt\ClientLogin\Profile;
use Bolt\Extension\Bolt\ClientLogin\Response\SuccessRedirectResponse;
use Bolt\Extension\Bolt\ClientLogin\Types;
use Hautelook\Phpass\PasswordHash;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Password login provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Local extends HandlerBase implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function login()
    {
        $response = parent::login();
        if ($response instanceof Response) {
            // User is logged in already, from whence they came return them now.
            return $response;
        }

        return $this->render();
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        if (!$token = $this->getTokenManager()->getToken(TokenManager::TOKEN_ACCESS)) {
            throw new InvalidAuthorisationRequestException('No token found for password endpoint.');
        }

        if (!$profile = $this->getRecordManager()->getProfileByProviderId('Password', $token->getResourceOwnerId())) {
            throw new InvalidAuthorisationRequestException('No matching profile record for token: ' . (string) $token);
        }

        $this->dispatchEvent(ClientLoginEvent::LOGIN_POST, $profile);

        // User is logged in already, from whence they came return them now.
        return new SuccessRedirectResponse('/');
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        return parent::logout();
    }

    /**
     * Render a password login page.
     *
     * @return Response
     */
    protected function render()
    {
        $formFields = FormFields::Password();
        $this->app['boltforms']->makeForm(Types::FORM_NAME_PASSWORD, 'form', [], []);
        $this->app['boltforms']->addFieldArray(Types::FORM_NAME_PASSWORD, $formFields['fields']);

        if ($this->request->isMethod('POST')) {
            // Validate the form data
            $form = $this->app['boltforms']
                ->getForm(Types::FORM_NAME_PASSWORD)
                ->handleRequest($this->request);
            $formData = $form->getData();

            // Validate against saved password data
            if ($form->isValid() && $this->check($formData)) {
                $profile = $this->getRecordManager()->getAccountByResourceOwnerId($formData['username']);
                if (!$profile) {
                    throw new InvalidAuthorisationRequestException('No matching profile found');
                }
                $response = new RedirectResponse($this->app['clientlogin.provider']->getBaseAuthorizationUrl());

                return $response;
            }
        }

        // Get password prompt
        $html = $this->app['clientlogin.ui']->displayPasswordPrompt();

        return new Response($html, Response::HTTP_OK);
    }

    /**
     * Check the password and login data.
     *
     * @param array $formData
     *
     * @throws InvalidAuthorisationRequestException
     *
     * @return boolean
     */
    protected function check($formData)
    {
        if (empty($formData['username']) || empty($formData['password'])) {
            throw new InvalidAuthorisationRequestException('Empty username or password data provided for password login request.');
        }

        // Look up a user profile
        $profile = $this->getRecordManager()->getAccountByResourceOwnerId($formData['username']);

        // If the profile doesn't exist, then we just want to warn of an invalid
        // combination, and check the stored hash versus the POSTed one.
        if ($profile !== false && $this->getHasher()->CheckPassword($formData['password'], $profile['password'])) {
            return true;
        }

        $this->setInvaildPasswordError($formData);

        return false;
    }

    /**
     * Handle the password logging, etc.
     *
     * @param array $formData
     */
    protected function setInvaildPasswordError($formData)
    {
        $this->setDebugMessage(sprintf('No user profile record found for %s', $formData['username']));
        $this->app['boltforms']->getForm(Types::FORM_NAME_PASSWORD)->addError(new FormError('Invalid user name or password.'));
    }

    /**
     * Get an instance of the password hasher.
     *
     * @return PasswordHash
     */
    private function getHasher()
    {
        return new PasswordHash(12, true);
    }
}
