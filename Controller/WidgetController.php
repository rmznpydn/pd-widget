<?php

/**
 * This file is part of the pd-admin pd-widget package.
 *
 * @package     pd-widget
 * @license     LICENSE
 * @author      Kerem APAYDIN <kerem@apaydin.me>
 * @link        https://github.com/appaydin/pd-widget
 */

namespace Pd\WidgetBundle\Controller;

use Pd\WidgetBundle\Entity\WidgetUser;
use Pd\WidgetBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Widget Actions.
 *
 * @author Kerem APAYDIN <kerem@apaydin.me>
 */
class WidgetController extends AbstractController
{
    /**
     * Change Widget Status.
     *
     * @param Request         $request
     * @param WidgetInterface $widget
     * @param string          $widgetId
     * @param bool            $status
     *
     * @return RedirectResponse
     */
    public function status(Request $request, WidgetInterface $widget, string $widgetId, bool $status = true): RedirectResponse
    {
        // Build Widget
        $widgets = $widget->getWidgets();

        if (isset($widgets[$widgetId])) {
            // Get User Widgets
            $widgetConfig = $this->getDoctrine()
                    ->getRepository('PdWidgetBundle:WidgetUser')
                    ->findOneBy(['owner' => $this->getUser()]) ?? (new WidgetUser())->setOwner($this->getUser());

            // Add Config Parameters
            $widgetConfig->addWidgetConfig($widgetId, ['status' => $status]);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($widgetConfig);
            $em->flush();
        }

        // Response
        return $this->redirect($request->headers->get('referer', $this->generateUrl($this->getParameter('pd_widget.return_route'))));
    }

    /**
     * Change Widget Configuration.
     *
     * @param Request         $request
     * @param WidgetInterface $widget
     * @param CacheInterface  $cache
     * @param string          $widgetId
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return RedirectResponse
     */
    public function configs(Request $request, WidgetInterface $widget, CacheInterface $cache, string $widgetId): RedirectResponse
    {
        // Build Widget
        $widgets = $widget->getWidgets();

        if (isset($widgets[$widgetId])) {
            // Get User Widgets
            $widgetConfig = $this->getDoctrine()
                    ->getRepository('PdWidgetBundle:WidgetUser')
                    ->findOneBy(['owner' => $this->getUser()]) ?? (new WidgetUser())->setOwner($this->getUser());

            // Add or Remove Config Parameters
            if ($request->get('remove')) {
                $widgetConfig->removeWidgetConfig($widgetId, $widgets[$widgetId]->getConfigProcess($request) ?? []);
            } else {
                $widgetConfig->addWidgetConfig($widgetId, $widgets[$widgetId]->getConfigProcess($request) ?? []);
            }

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($widgetConfig);
            $em->flush();

            // Flush Widget Cache
            $cache->delete($widgetId.$this->getUser()->getId());
        }

        // Response
        return $this->redirect($request->headers->get('referer', $this->generateUrl($this->getParameter('pd_widget.return_route'))));
    }

    /**
     * Change Widget Order.
     *
     * @param WidgetInterface $widget
     * @param string          $widgetId
     * @param int             $order
     *
     * @return JsonResponse
     */
    public function order(WidgetInterface $widget, string $widgetId, int $order): JsonResponse
    {
        // Build Widget
        $widgets = $widget->getWidgets();

        if (isset($widgets[$widgetId])) {
            // Get User Widgets
            $widgetConfig = $this->getDoctrine()
                    ->getRepository('PdWidgetBundle:WidgetUser')
                    ->findOneBy(['owner' => $this->getUser()]) ?? (new WidgetUser())->setOwner($this->getUser());

            // Add Config Parameters
            $widgetConfig->addWidgetConfig($widgetId, ['order' => $order]);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($widgetConfig);
            $em->flush();
        }

        // Response
        return $this->json([
            'result' => 'success',
        ]);
    }
}
