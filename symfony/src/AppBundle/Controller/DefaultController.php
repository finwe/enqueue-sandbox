<?php

namespace AppBundle\Controller;

use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Liip\ImagineBundle\Async\ResolveCache;
use Liip\ImagineBundle\Async\Topics as LiipImagineTopics;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        /** @var ProducerInterface $producer */
        $producer = $this->get('enqueue.producer');

        $producer->send('foo_topic', 'Hello world');

        $producer->send('bar_topic', ['bar' => 'val']);

        $message = new Message();
        $message->setBody('baz');
        $producer->send('baz_topic', $message);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/liip_imagine/resolve_all", name="liip_imagine_resolve_all")
     */
    public function liipImagineResolveAllAction(Request $request)
    {
        /** @var ProducerInterface $producer */
        $producer = $this->get('enqueue.producer');

        $producer->send(LiipImagineTopics::RESOLVE_CACHE, new ResolveCache('kitten.jpg'));
        $producer->send(LiipImagineTopics::RESOLVE_CACHE, new ResolveCache('castle.jpg', ['thumbnail_223x223']));

        return new Response(<<<HTML
<p> The controller sends a message to resolve cache for two images kitten.jpg and castle.jpg.</p>
<p> Before you run this make sure web/media folder is empty and the consumer is up and running.</p>
<p> After you did the request there must be some messages in the queue and files in /web/media folder must appear.</p>
HTML
        );
    }

    /**
     * @Route("/rabbitmqbundle/readme", name="rabbitmqbundle_readme")
     */
    public function rabbitmqBundleReadmeAllAction(Request $request)
    {
        $msg = array('user_id' => 1235, 'image_path' => '/path/to/new/pic.png');

        $this->get('old_sound_rabbit_mq.upload_picture_producer')->publish(serialize($msg));
        $this->get('enqueue.producer')->send('upload_picture', $msg);




        return new Response(<<<HTML
<p>The controller sends a message using the RabbitMQBundle.</p>
<p>Check upload-picture queue.</p>
HTML
        );
    }
}
