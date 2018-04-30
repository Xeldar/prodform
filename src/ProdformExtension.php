<?php
namespace Bolt\Extension\Prod\Prodform;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\SimpleExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;

class ProdformExtension extends SimpleExtension
{

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'prodform' => [
                'extentions' => array('JPG','jpg','JPEG','jpeg','png','doc','docx','ppt','pptx','xls','xlsx'),
                'maxisze' => 10485760,
            ]
        ];
    }

    /**
     * Define events to listen to here.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(BoltFormsEvents::SUBMISSION_PRE_PROCESSOR,  array($this, 'myPostSubmit'));
    }

    public function myPostSubmit($event)
    {
        $config = $this->getConfig();
        $data = $event->getData();
        if(isset($data->_fields['upload'])) {
            $uploadField = $data->_fields['upload'];
            $maxSize = $config['prodform']['maxisze'];
            $authExt = $config['prodform']['extentions'];
            $ext = strtolower(pathinfo($uploadField->getClientOriginalName(),PATHINFO_EXTENSION));
            $fileSize = filesize($uploadField->getPathName());

            if(!in_array($ext,$authExt)) {
                throw new FormValidationException('Extension non autorisée, liste des extensions autorisées : ' . implode(', ',$authExt));
            } else if($fileSize > $maxSize) {
                throw new FormValidationException('Fichier trop lourd, poids maximum : ' . ($maxSize / 1024 / 1024) .  ' Mo');
            }

        }
    }
}
