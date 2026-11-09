<?php
namespace App\Controller;

use App\Entity\BudgetTransaction;
use App\Form\BudgetTransactionType;
use App\Repository\BudgetTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget/transaction')]
final class BudgetTransactionController extends AbstractController
{
    #[Route(name: 'app_budget_transaction_index', methods: ['GET'])]
    
public function index(
    Request $request,
    BudgetTransactionRepository $budgetTransactionRepository
): Response
{
    $perPage = 10;

    $total = $budgetTransactionRepository->count([]);

    $totalPages = max(1, (int) ceil($total / $perPage));

    $page = $request->query->getInt('page', 1);
    $page = max(1, min($page, $totalPages));

    $transactions = $budgetTransactionRepository->findBy(
        [],
        ['createdAt' => 'DESC', 'id' => 'DESC'],
        $perPage,
        ($page - 1) * $perPage
    );

    return $this->render('budget_transaction/index.html.twig', [
        'budget_transactions' => $transactions,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
}


    #[Route('/new', name: 'app_budget_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $budgetTransaction = new BudgetTransaction();
        $form = $this->createForm(BudgetTransactionType::class, $budgetTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($budgetTransaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_budget_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('budget_transaction/new.html.twig', [
            'budget_transaction' => $budgetTransaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_budget_transaction_show', methods: ['GET'])]
    public function show(BudgetTransaction $budgetTransaction): Response
    {
        return $this->render('budget_transaction/show.html.twig', [
            'budget_transaction' => $budgetTransaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_budget_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BudgetTransaction $budgetTransaction, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BudgetTransactionType::class, $budgetTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_budget_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('budget_transaction/edit.html.twig', [
            'budget_transaction' => $budgetTransaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_budget_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, BudgetTransaction $budgetTransaction, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$budgetTransaction->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($budgetTransaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_budget_transaction_index', [], Response::HTTP_SEE_OTHER);
    }
}
