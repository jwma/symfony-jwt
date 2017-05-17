<?php

namespace AppBundle\Command\User;

use AppBundle\Entity\AdminUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppCreateAdminUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:create-admin-user')
            ->setDescription('创建后台管理员用户')
            ->addArgument('username', InputArgument::REQUIRED, '用户名')
            ->addArgument('password', InputArgument::REQUIRED, '密码');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.default_entity_manager');

        $checkUser = $em->getRepository('AppBundle:AdminUser')
            ->findOneBy(['username' => $username]);

        if ($checkUser) {
            $output->writeln(sprintf('用户名为：%s 已存在，请使用其他用户名。', $username));
            return 1;
        }

        $user = new AdminUser();
        $passwordEncoder = $container->get('security.password_encoder');
        $encodedPassword = $passwordEncoder->encodePassword($user, $password);

        $user
            ->setUsername($username)
            ->setPassword($encodedPassword);

        $em->persist($user);
        $em->flush();
        $em->clear();

        $output->writeln(sprintf('成功新增 %s 管理员', $username));
    }
}
