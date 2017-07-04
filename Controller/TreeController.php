<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use PHPCR\Util\PathHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A controller to render the tree block.
 */
class TreeController extends Controller
{
    /**
     * @var string
     */
    private $template = 'SonataDoctrinePHPCRAdminBundle:Tree:tree.html.twig';

    /**
     * @var \PHPCR\SessionInterface
     */
    private $session;
    /**
     * @var array
     */
    private $treeConfiguration;

    /**
     * @param ManagerRegistry $manager
     * @param string          $sessionName
     * @param array           $treeConfiguration
     * @param string          $template
     */
    public function __construct(ManagerRegistry $manager, $sessionName, array $treeConfiguration, $template = null)
    {
        if ($template) {
            $this->template = $template;
        }

        $this->session = $manager->getConnection($sessionName);
        $this->treeConfiguration = $treeConfiguration;
    }

    /**
     * Renders a tree, passing the routes for each of the admin types (document types)
     * to the view.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function treeAction(Request $request)
    {
        $root = $request->attributes->get('root');

        return $this->render($this->template, array(
            'root_node' => $root,
            'tree_configuration' => $this->treeConfiguration,
        ));
    }

    /**
     * Reorder $moved (child of $parent) before or after $target.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function reorderAction(Request $request)
    {
        $parentPath = $request->get('parent');
        $dropedAtPath = $request->get('dropped');
        $targetPath = $request->get('target');
        $position = $request->get('position');

        if (null === $parentPath || null === $dropedAtPath || null === $targetPath) {
            return new JsonResponse(array('Parameters parent, dropped and target has to be set to reorder.'), Response::HTTP_BAD_REQUEST);
        }

        $before = 'before' == $position;
        $parentNode = $this->session->getNode($parentPath);
        $targetName = PathHelper::getNodeName($targetPath);
        if (!$before) {
            $nodesIterator = $parentNode->getNodes();
            $nodesIterator->rewind();
            while ($nodesIterator->valid()) {
                if ($nodesIterator->key() == $targetName) {
                    break;
                }
                $nodesIterator->next();
            }
            $targetName = null;
            if ($nodesIterator->valid()) {
                $nodesIterator->next();
                if ($nodesIterator->valid()) {
                    $targetName = $nodesIterator->key();
                }
            }
        }
        $parentNode->orderBefore($targetName, PathHelper::getNodeName($dropedAtPath));
        $this->session->save();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
