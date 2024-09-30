<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\CurlSender;
use App\Helper\SignatureHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Invoice;
use App\Entity\Callback;
use App\Form\InvoiceFormType;

class InvoiceController extends AbstractController
{
    private $curlSender;
    private $entityManager;

    public function __construct(
        CurlSender $curlSender, 
        EntityManagerInterface $entityManager,
        ManagerRegistry $doctrine
    ) {
        $this->curlSender = $curlSender;
        $this->entityManager = $entityManager;
        $this->doctrine = $doctrine;
    }
    
    /**
     * METHOD FOR CREATING INVOICE
     */
    #[Route('/payment-form', name: 'app_payment')]
    public function index(Request $request, ManagerRegistry $doctrine)
    {
        try {
            $form = $this->createForm(InvoiceFormType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $data = $form->getData();
                
                $requestdata = $request->server->all();
                $orderId = (int)$data['merchant_order_id'];

                $invoiceRepository = $this->doctrine->getRepository(Invoice::class);
                $existingInvoiceNumber = $invoiceRepository->findExistingInvoice($orderId);

                if ($existingInvoiceNumber) {
                    return $this->redirectToRoute('error_page', ['error_message' => 'Invoice number already exists.'], 303);
                }

                $invoice = new Invoice();
                $invoice->setUserId((int)$data['id']);
                $invoice->setMerchantOrderId($orderId);
                $invoice->setAmount($data['amount']);
                $invoice->setCountry($data['country']);
                $invoice->setCurrency($data['currency']);
                $invoice->setPaymentMethod($data['payment_method']);
                $invoice->setStatus('CREATED');
                $invoice->setRequestData($requestdata);
            
                $this->entityManager->persist($invoice);
                $this->entityManager->flush();

                $invoiceId = $invoice->getId();

                $data['description'] = 'Invoice test description.';
                $data['client_ip'] = $request->getClientIp();

                $rawData = json_encode($data);

                $secretKey = $this->getParameter('app.secretKey');

                $signature = SignatureHelper::signData($rawData, $secretKey);

                $paymentInfo = $this->curlSender->sendPaymentRequest($rawData, $signature);         
                $paymentInfo = json_decode($paymentInfo, true);

                $invoice = $this->entityManager->getRepository(Invoice::class)->find($invoiceId);
                $invoice->setResponseData($paymentInfo);

                if ($paymentInfo['status'] === 201) {

                    $invoice->setStatus('PENDING');
                    $this->entityManager->flush();

                    $paymentInfo = $paymentInfo['payment_info'];
                    unset($paymentInfo['metadata']);

                    return $this->redirectToRoute('notification_page', ['paymentInfo' => $paymentInfo], 303);
                }

                if ($paymentInfo['status'] === 400) {

                    $invoice->setStatus('ERROR');
                    $this->entityManager->flush();

                    return $this->redirectToRoute('error_page', ['error_message' => $paymentInfo['message']], 303);
                }
            }

            return $this->render('payment/index.html.twig', [
                'form' => $form
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     * METHOD FOR DISPLAYING ERRORS
     */
    #[Route('/error-page', name: 'error_page')]
    public function showErrorMessage(Request $request): Response
    {
        $errorMessage = $request->query->get('error_message');

        return new Response($errorMessage);
    }

    /**
     * METHOD FOR DISPLAYING PAYMENT INFO
     */
    #[Route('/notification-page', name: 'notification_page')]

    public function showPaymentInfo(Request $request)
    {
        $query_parameters = $request->query->all();
        $paymentInfo = $query_parameters['paymentInfo'];

        return $this->render('payment/notification-page.html.twig', [
            'paymentInfo' => $paymentInfo
        ]);
    }

    /**
     * METHOD FOR HANDLING CALLBACKS
     */
    #[Route('/callback-handle', name: 'callback-handle')]
    public function handleCallback(Request $request)
    {
        $requestParameters = $request->request->all();
        $orderId = (int)$requestParameters['merchant_order_id'];
        $timestamp = $requestParameters['timestamp'];

        $invoiceRepository = $this->doctrine->getRepository(Invoice::class);
        $invoice = $invoiceRepository->findOneBy(['merchant_order_id' => $orderId]);

        $callbackRepository = $this->doctrine->getRepository(Callback::class);
        $callbackExists = $callbackRepository->findOneBy(['merchant_order_id' => $orderId]);

        if ($callbackExists) {

            $invoice->setStatus('REJECTED');
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            return new Response('Callback rejected - Duplicate callback.', 409);
        }

        $requestdata = $request->server->all();

        $callback = new Callback();
        $callback->setMerchantOrderId($orderId);
        $callback->setCallbackData($requestdata);

        $this->entityManager->persist($callback);
        $this->entityManager->flush();


        //HARDCODED JUST FOR TESTING PURPOSES(IT CAN BE ASSOCIATED WITH CERTAIN INVOICE)
        $invoiceSignature = 'NzUzOTRhYWJkZDk1YmZkOGMwN2RjNzViZTExNzY5MTA5M2IyZTBhMGYzYTRhODRjYTE0N2RkN2Y5NTBhYjA1Mw==';
        
        //ALSO HARDCODED IN PSP APP
        $confirmSignature = $request->headers->get('X-signature');

        if (!$invoice) {
            return new Response('Invoice does not exist', 404);
        }
        
        //SEPARATE METHOD CAN BE MADE FOR MORE DETAILED CHECK
        if ($confirmSignature !== $invoiceSignature) {

            $invoice->setStatus('ERROR');
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            return new Response('Signature is not valid.', 500);
        }

        //WE ARE JUST SIMULATING EXPIRATION AFTER 5 MINUTES - THIS SHOULD BE DYNAMIC
        if (($timestamp) > (time() + 300)) {

            $invoice->setStatus('EXPIRED');
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            return new Response('Transaction expired.', 419);
        }

        $invoice->setStatus('SUCCESSFUL');
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return new Response('Invoice successfully updated.', 200);
    }
}
