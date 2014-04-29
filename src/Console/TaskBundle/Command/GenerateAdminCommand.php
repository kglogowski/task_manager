<?php

namespace Console\TaskBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateAdminCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('admin:generate')
                ->setDescription('Generuje admina dla entity')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dialog = $this->getHelperSet()->get('dialog');
        
        $path = $dialog->ask($output, 'Podaj ścieżke do klasy: ', '');
        $arrPath = explode('\\', $path);
        $className = $arrPath[count($arrPath)-1];
        $lname = strtolower($className);
        
        
        $fs = new Filesystem();
        $m = $this->getContainer()->get('doctrine')->getManager();
        $class = new $path();

        $arrFields = $m->getClassMetadata($path)->getFieldNames();
        
        $fs->touch('src/App/AdminBundle/Controller/'.$className.'Controller.php');

        $form = "\t\$form = \$this->createFormBuilder(\$object)\n";
        foreach ($arrFields as $value) {
            if($value != 'id') {
                $form .= "\t\t->add('".$value."', 'text', array('attr' => array ( 'class' =>  'form-control' ) ))\n";
            }
        }
        $form .= "\t\t->add('Zapisz', 'submit', array('attr' => array('class' => 'btn btn-success btn-sm')))\n\t\t\t->getForm();\n";
        
        $ct = "<?php

namespace App\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Data\DatabaseBundle\Entity\\$className;

class ".$className."Controller extends Controller
{

    public function newAction() {
        \$m = \$this->getDoctrine()->getManager();
        \$object = new $className();
        $form
        \$request = \$this->getRequest();
        if(\$request->isMethod('POST')) {
            \$form->handleRequest(\$request);
            if(\$form->isValid()) {
                \$m->persist(\$object);
                \$m->flush();
                return \$this->redirect(\$this->generateUrl('".$lname."_show'));
            }
        }
        return \$this->render('AdminBundle:$className:new.html.twig', array('form' => \$form->createView()));
    }

    public function showAction(\$page) {
        \$m = \$this->getDoctrine()->getManager();
        \$offset = (\$page-1)*10;
        \$limit = 10;
        return \$this->render('AdminBundle:$className:show.html.twig', array('count' => count(\$m->getRepository('DataDatabaseBundle:$className')->findAll()) ,'page' => \$page, 'collection' => \$m->getRepository('DataDatabaseBundle:$className')->findBy(array(),array('id' => 'ASC'), \$limit, \$offset)));
    }
    
    public function deleteAction(\$id) {
        \$m = \$this->getDoctrine()->getManager();
        \$object = \$m->getRepository('DataDatabaseBundle:$className')->findOneById(\$id);
        \$m->remove(\$object);
        \$m->flush();
        return \$this->redirect(\$this->generateUrl('".$lname."_show'));
    }

    public function editAction(\$id) {
        \$m = \$this->getDoctrine()->getManager();
        \$object = \$m->getRepository('DataDatabaseBundle:$className')->findOneById(\$id);
        $form
        \$request = \$this->getRequest();
        if(\$request->isMethod('POST')) {
            \$form->handleRequest(\$request);
            if(\$form->isValid()) {
                \$m->persist(\$object);
                \$m->flush();
                return \$this->redirect(\$this->generateUrl('".$lname."_show'));
            }
        }
        return \$this->render('AdminBundle:$className:edit.html.twig', array('form' => \$form->createView()));
    }
}
";
        file_put_contents('src/App/AdminBundle/Controller/'.$className.'Controller.php', $ct);

        $fs->mkdir('src/App/AdminBundle/Resources/views/'.$className);
        $fs->touch('src/App/AdminBundle/Resources/views/'.$className.'/show.html.twig');
        $fs->touch('src/App/AdminBundle/Resources/views/'.$className.'/edit.html.twig');
        $fs->touch('src/App/AdminBundle/Resources/views/'.$className.'/new.html.twig');
        $showNg = '';
        $showTd = '';
        foreach ($arrFields as $i => $value) {
            $showNg .= "\t\t" . '<th>' . ucfirst($value) . '</th>' . "\n";
            $showTd .= "\t\t" . '<td>{{ object.' . $value . ' }}</td>' . "\n";
            if ($i == count($arrFields) - 1) {
                $showNg .= "\t\t" . '<th>Akcja</th>' . "\n";
                $showTd .= "\t\t" . '<td><div><a href="{{ url(\''.$lname.'_edit\', {\'id\': object.id}) }}"><span class="glyphicon glyphicon-pencil"></span></a><a href="{{ url(\''.$lname.'_delete\', {\'id\': object.id}) }}"><span class="glyphicon glyphicon-remove span-remove"></span></a></div></td>' . "\n";
            }
        }

        $show = "
{% extends \"::admin.html.twig\" %}
{% block content %}
<div class=\"ct\">
    {{ include('AppHelperBundle:bootstrap:naglowek.html.twig', {h1: '$className', small: 'Lista'}) }}
    <table class=\"table table-hover\">
        <tr>
" . $showNg . "
        </tr>
        {% for object in collection %}
        <tr>
" . $showTd . "
        </tr>
        {% endfor %}
    </table>
    <form method=\"get\" action=\"{{ url('".$lname."_new') }}\">
        <div class=\"btn\"><button class=\"btn btn-success\">New</button></div>
    </form>
    {{ include('AppHelperBundle:bootstrap:pagination.html.twig', {page: page, count: count }) }}
</div>
{% endblock content %}
";
        file_put_contents('src/App/AdminBundle/Resources/views/'.$className.'/show.html.twig', $show);


        $edit = "{% extends \"::admin.html.twig\" %}
{% block content %}
<div class='ct' >
    {{ include('AppHelperBundle:bootstrap:naglowek.html.twig', {h1: '$className', small: 'Edycja'}) }}
    {{ include('AppHelperBundle:form:standard_form.html.twig', {form: form}) }}
    {{ include('AppHelperBundle:bootstrap:return_link.html.twig', { link: '".$lname."_show' }) }}
</div>
{% endblock content %}
";
        
        $new = "{% extends \"::admin.html.twig\" %}
{% block content %}
<div class='ct' >
    {{ include('AppHelperBundle:bootstrap:naglowek.html.twig', {h1: '$className', small: 'Nowy'}) }}
    {{ include('AppHelperBundle:form:standard_form.html.twig', {form: form}) }}
    {{ include('AppHelperBundle:bootstrap:return_link.html.twig', { link: '".$lname."_show' }) }}
</div>
{% endblock content %}
";
        
        file_put_contents('src/App/AdminBundle/Resources/views/'.$className.'/edit.html.twig', $edit);
        file_put_contents('src/App/AdminBundle/Resources/views/'.$className.'/new.html.twig', $new);



        $output->writeln("Routing:");
        $output->writeln("".$lname."_show:
    pattern: /".$lname."-show/{page}
    defaults:  { _controller: AdminBundle:$className:show, page: 1 }");
        
        $output->writeln("".$lname."_edit:
    pattern: /".$lname."-edit/{id}
    defaults:  { _controller: AdminBundle:$className:edit }");
        
        $output->writeln("".$lname."_delete:
    pattern: /".$lname."-delete/{id}
    defaults:  { _controller: AdminBundle:$className:delete }");

        $output->writeln("".$lname."_new:
    pattern: /".$lname."-new
    defaults:  { _controller: AdminBundle:$className:new }");
        
    }

}
