<?php

namespace App\MessageHandler;

use App\Message\RecipePDFMessage;
use App\Repository\RecipeRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
final class RecipePDFMessageHandler{

    function __construct(
        #[Autowire('%kernel.project_dir%/public/pdfs')]
        private readonly string $path,
        #[Autowire('%env(GOTENBERG_ENDPOINT)%')]
        private readonly string $gotenbergEndpoint,
        private readonly RecipeRepository $recipeRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function __invoke(RecipePDFMessage $message)
    {
        /* file_put_contents($this->path . '/' . $message->id. '.pdf', ''); */

        $slug = $this->recipeRepository->findOneBy(['id' => $message->id])->getSlug();

        $process = new Process([
            'curl', '--request', 'POST',
            sprintf('%s/forms/chromium/covert/url', $this->gotenbergEndpoint),
            '--form',
            sprintf('url=%s', $this->urlGenerator->generate('admin.recipes.show', ['id' => $message->id, 'slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL)),
            '-O',
            sprintf("%s%s.pdf", $this->path, $message->id)
        ]);
        $process->run();
        if (!$process->isSuccessful()) throw new ProcessFailedException($process);
    }
}
