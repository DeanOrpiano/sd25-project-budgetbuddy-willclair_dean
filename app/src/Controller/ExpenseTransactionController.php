<?php

namespace App\Controller;

use App\Entity\ExpenseTransaction;
use App\Form\ExpenseTransactionType;
use App\Repository\ExpenseTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/expense/transaction')]
final class ExpenseTransactionController extends AbstractController
{
    #[Route(name: 'app_expense_transaction_index', methods: ['GET'])]
    public function index(ExpenseTransactionRepository $expenseTransactionRepository): Response
    {
        return $this->render('expense_transaction/index.html.twig', [
            'expense_transactions' => $expenseTransactionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_expense_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $expenseTransaction = new ExpenseTransaction();
        $form = $this->createForm(ExpenseTransactionType::class, $expenseTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($expenseTransaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_expense_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expense_transaction/new.html.twig', [
            'expense_transaction' => $expenseTransaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expense_transaction_show', methods: ['GET'])]
    public function show(ExpenseTransaction $expenseTransaction): Response
    {
        return $this->render('expense_transaction/show.html.twig', [
            'expense_transaction' => $expenseTransaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_expense_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ExpenseTransaction $expenseTransaction, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExpenseTransactionType::class, $expenseTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_expense_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expense_transaction/edit.html.twig', [
            'expense_transaction' => $expenseTransaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expense_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, ExpenseTransaction $expenseTransaction, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$expenseTransaction->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($expenseTransaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_expense_transaction_index', [], Response::HTTP_SEE_OTHER);
    }
}
