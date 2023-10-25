<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Hasło nie może być puste!',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Hasło powinno mieć przynajmniej {{ limit }} znaków!',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern'=>"/.*[A-Z].*/",
                            'message'=>"Hasło musi mieć minimum jedną dużą literę"
                        ]),
                        new Regex([
                            'pattern'=>"/.*[a-z].*/",
                            'message'=>"Hasło musi mieć minimum jedną małą literę"
                        ]),
                        new Regex([
                            'pattern'=>"/.*[0-9].*/",
                            'message'=>"Hasło musi mieć minimum jedną cyfrę"
                        ]),
                        new Regex([
                            'pattern'=>"/.*[\W].*/",
                            'message'=>"Hasło musi mieć minimum jeden znak specjalny"
                        ]),
                        new Regex([
                            'pattern'=>"/^[^\s]*$/",
                            'message'=>"Hasło nie może zawierać spacji"
                        ])
                    ],'label'=>'Nowe hasło',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'nowe hasło'
                    ]

                ],
                'second_options' => [
                    'label'=>'Powtórz nowe hasło',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'powtórz nowe hasło'
                    ]
                ],
                'invalid_message' => 'Hasła nie pasują do siebie!',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
