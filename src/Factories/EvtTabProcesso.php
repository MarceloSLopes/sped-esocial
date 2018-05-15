<?php

namespace NFePHP\eSocial\Factories;

/**
 * Class eSocial EvtTabProcesso Event S-1070 constructor
 * READ for 2.4.2 layout
 *
 * @category  library
 * @package   NFePHP\eSocial
 * @copyright NFePHP Copyright (c) 2017
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-esocial for the canonical source repository
 */

use NFePHP\Common\Certificate;
use NFePHP\eSocial\Common\Factory;
use NFePHP\eSocial\Common\FactoryInterface;
use stdClass;

class EvtTabProcesso extends Factory implements FactoryInterface
{
    /**
     * @var int
     */
    public $sequencial;

    /**
     * @var string
     */
    protected $evtName = 'evtTabProcesso';

    /**
     * @var string
     */
    protected $evtAlias = 'S-1070';

    /**
     * Parameters patterns
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Constructor
     *
     * @param string $config
     * @param stdClass $std
     * @param Certificate $certificate
     */
    public function __construct(
        $config,
        stdClass $std,
        Certificate $certificate
    ) {
        parent::__construct($config, $std, $certificate);
    }

    /**
     * Node constructor
     */
    protected function toNode()
    {
        $ideEmpregador = $this->node->getElementsByTagName('ideEmpregador')->item(0);
        //o idEvento pode variar de evento para evento
        //então cada factory individualmente terá de construir o seu
        $ideEvento = $this->dom->createElement("ideEvento");
        $this->dom->addChild(
            $ideEvento,
            "tpAmb",
            $this->tpAmb,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "procEmi",
            $this->procEmi,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "verProc",
            $this->verProc,
            true
        );
        $this->node->insertBefore($ideEvento, $ideEmpregador);

        //tag deste evento em particular
        $info = $this->dom->createElement("infoProcesso");

        //tag comum a todos os modos
        $ide = $this->dom->createElement("ideProcesso");
        $this->dom->addChild(
            $ide,
            "tpProc",
            $this->std->tpproc,
            true
        );
        $this->dom->addChild(
            $ide,
            "nrProc",
            $this->std->nrproc,
            true
        );
        $this->dom->addChild(
            $ide,
            "iniValid",
            $this->std->inivalid,
            true
        );
        $this->dom->addChild(
            $ide,
            "fimValid",
            ! empty($this->std->fimvalid) ? $this->std->fimvalid : null,
            false
        );
        //seleção do modo
        if ($this->std->modo == 'INC') {
            $node = $this->dom->createElement("inclusao");
        } elseif ($this->std->modo == 'ALT') {
            $node = $this->dom->createElement("alteracao");
        } else {
            $node = $this->dom->createElement("exclusao");
        }
        $node->appendChild($ide);

        $dados = $this->dom->createElement("dadosProc");
        $this->dom->addChild(
            $dados,
            "indAutoria",
            ! empty($this->std->dadosproc->indautoria)
                ? $this->std->dadosproc->indautoria
                : null,
            false
        );
        $this->dom->addChild(
            $dados,
            "indMatProc",
            $this->std->dadosproc->indmatproc,
            true
        );
        $this->dom->addChild(
            $dados,
            "observacao",
            !empty($this->std->dadosproc->observacao) ? $this->std->dadosproc->observacao : null,
            false
        );
        if (! empty($this->std->dadosproc->dadosprocjud)) {
            $dadosProcJud = $this->dom->createElement("dadosProcJud");
            $this->dom->addChild(
                $dadosProcJud,
                "ufVara",
                $this->std->dadosproc->dadosprocjud->ufvara,
                true
            );
            $this->dom->addChild(
                $dadosProcJud,
                "codMunic",
                $this->std->dadosproc->dadosprocjud->codmunic,
                true
            );
            $this->dom->addChild(
                $dadosProcJud,
                "idVara",
                $this->std->dadosproc->dadosprocjud->idvara,
                true
            );
            $dados->appendChild($dadosProcJud);
        }
        if (! empty($this->std->dadosproc->infosusp)) {
            foreach ($this->std->dadosproc->infosusp as $susp) {
                $infoSusp = $this->dom->createElement("infoSusp");
                $this->dom->addChild(
                    $infoSusp,
                    "codSusp",
                    $susp->codsusp,
                    true
                );
                $this->dom->addChild(
                    $infoSusp,
                    "indSusp",
                    $susp->indsusp,
                    true
                );
                $this->dom->addChild(
                    $infoSusp,
                    "dtDecisao",
                    $susp->dtdecisao,
                    true
                );
                $this->dom->addChild(
                    $infoSusp,
                    "indDeposito",
                    $susp->inddeposito,
                    true
                );
                $dados->appendChild($infoSusp);
            }
        }
        $node->appendChild($dados);

        if (! empty($this->std->novavalidade) && $this->std->modo == 'ALT') {
            $newVal       = $this->std->novavalidade;
            $novaValidade = $this->dom->createElement("novaValidade");
            $this->dom->addChild(
                $novaValidade,
                "iniValid",
                $newVal->inivalid,
                true
            );
            $this->dom->addChild(
                $novaValidade,
                "fimValid",
                ! empty($newVal->fimvalid) ? $newVal->fimvalid : null,
                false
            );
            $node->appendChild($novaValidade);
        }

        $info->appendChild($node);
        //finalização do xml
        $this->node->appendChild($info);
        $this->eSocial->appendChild($this->node);
        $this->sign();
    }
}
