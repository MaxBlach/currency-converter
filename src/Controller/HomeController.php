<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $result = null;
        static $message = "";
        $time = date('H:i:s');

        // Mise en place du formulaire
        $form = $this->createFormBuilder()
            ->add('value1', NumberType::class, [
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new Assert\GreaterThan(0, message: 'La valeur doit être positive')
                ]
            ])
            // On ne peut choisir pour l'instant que entre deux devises.
            ->add('symbol1', ChoiceType::class, [
                'choices' => [
                    'Euro' => 'EUR',
                    'US Dollar' => 'USD'
                ]
            ])
            ->add('value2', NumberType::class, [
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new Assert\GreaterThan(0, message: 'La valeur doit être positive')
                ]
            ])
            ->add('symbol2', ChoiceType::class, [
                'choices' => [
                    'Euro' => 'EUR',
                    'US Dollar' => 'USD'
                ]
            ])
            ->getForm()
        ;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            //Si on se retrouve avec trop de complexité, on peut utiliser un switch case avec un cas différent pour la première devise

            //Si les deux devises sont les mêmes on additionne les valeurs
            if ($data['symbol1'] == $data['symbol2']) {
                $result['value'] = $data['value1'] + $data['value2'];
            }
            //Si la première valeur est en euro on converti la valeur en la seconde devise (en dollar dans ce cas)
            else if( $data['symbol1'] == 'EUR' ){
                $result['value'] = $data['value2'] + $data['value1'] * $this->getPercentageOfCurrencyToDollar($data['symbol1']);
            }
            //Si la première valeur est en dollar on converti la valeur en la seconde devise (en euro dans ce cas)
            else if( $data['symbol1'] == 'USD'){
                $result['value'] = $data['value2'] + $data['value1'] * $this->getPercentageOfCurrencyToEuro($data['symbol1']);
            }

            $result['symbol'] = $data['symbol2'];

            //On concatène le message à chaque fois pour ne pas perdre sa valeur
            $message = $message . $data['value1'] . $data['symbol1'] . "+" . $data['value2'] . $data['symbol2'] . '=' . $result['value'] . $result['symbol'] . "\r\n";

            //Si il est minuit, alors on envoie la variable "message" qui contient l'historique de tout les calsul du jour
            //Ensuite, on la remet à zero pour le jour suivant
            if($time = "00:00:00"){
                //mail("adresse.client@mail.fr","Histrorique du jour",$message);
                $message = "";
            }
        }


        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }
    //on peut créer une fonction pour chaque devise
    private function getPercentageOfCurrencyToEuro(string $currency): float {
        return match ($currency) {
            'USD' => 0.9,
            default => 1,
        };
    }

    private function getPercentageOfCurrencyToDollar(string $currency): float {
        return match ($currency) {
            'EUR' => 1.11,
            default => 1,
        };
    }
}
