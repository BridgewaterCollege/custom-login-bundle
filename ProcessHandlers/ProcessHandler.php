<?php
namespace Tweisman\Bundle\CustomLoginBundle\ProcessHandlers;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Type: Process Handler
 * Tyler Weisman: Process handler's in my view are a layer of abstraction that represents the "process" that takes place on a specific page or as part of business logic/work flow. They make it easy to tie multiple entities/models
 * together into one cohesive reusable micro-service. The main ProcessHandler class consists of a couple functions that are re-usable across most any "process/service" you write in Symfony.
 * They also allow me to keep my controllers light by off loading business processes to the process handler, making them more re-usable and also allowing me to have less controllers because I can sometimes combine
 * multiple routes under one controller function.
 */
class ProcessHandler
{
    protected $validator_service;
    protected $em;
    protected $logger;

    public $errorsArray = array(); // error's array set on validateEntity
    public $pageCanSubmit = false; // determines if the current screen/page can submit (flag)

    public function __construct(ValidatorInterface $validator_service, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->validator_service = $validator_service;
        $this->em = $em;
        $this->logger = $logger;
    }

    public function validateDateIsFuture($dateSelected) {
        // Check the date's type that was passed into this function... integer, string etc...
        $currentDate = strtotime(date('Y-m-d'));
        if (is_string($dateSelected)) {
            $dateSelected = strtotime($dateSelected);
        }

        if ($currentDate > $dateSelected)
            return false;
        else
            return true;
    }

    public function validateEntity($entity, $validationGroups) {
        /**
         * Description: leverages Symfony's build in validator to validate an entity is "able to be submitted or persisted" to the server or the database. Uses annotation based constraints
         * in the entity itself to specify how to validate each field/variable.
         * $entity, pretty self explanatory it's the entity/model being validated and should be an object
         * $validationGroups is an array and are constraint groups that can be used to validate a subset of variables in an entity.
         */
        $errors = $this->validator_service->validate($entity, null, $validationGroups);
        $this->errorsArray = array();
        if (count($errors) > 0) {
            $errorsArray = array();
            foreach ($errors as $error)
                $this->errorsArray[$error->getPropertyPath()] = $error->getMessage();
        }
        $this->pageCanSubmit = (count($errors) > 0 ? false : true);
    }

    public function paginateQuery($query, $page = 1, $limit = 10) {
        /** Paginates a doctrine query to only return the requested page worth of results (defaults: page 1, limit 10 objects) */
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit
        return $paginator;
    }
}