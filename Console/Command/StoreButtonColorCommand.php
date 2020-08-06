<?php

namespace Scandi\ChangeColorByConsole\Console\Command;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StoreButtonColorCommand
 * @package Scandi\ChangeColorByConsole\Console\Command
 */
class StoreButtonColorCommand extends Command
{
    /**
     *
     */
    const CONFIG_PATH = 'design/head/includes';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriterInterface
     */
    protected $_writerInterface;

    /**
     * @var TypeListInterface
     */
    protected $_typeListInterface;

    /**
     * @var Pool
     */
    protected $_pool;

    /**
     * StoreButtonColorCommand constructor.
     * @param StoreManagerInterface $storeManager
     * @param WriterInterface $writer
     * @param TypeListInterface $typeList
     * @param Pool $pool
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        WriterInterface $writer,
        TypeListInterface $typeList,
        Pool $pool
    ) {
        $this->storeManager = $storeManager;
        $this->_writerInterface = $writer;
        $this->_typeListInterface = $typeList;
        $this->_pool = $pool;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('scandiweb:color-change')
            ->setDescription('Changes the buttons color based in store ID')
            ->setDefinition([
                new InputArgument(
                    'colorHex',
                    InputArgument::REQUIRED,
                    'The color for the buttons in hex format'
                ),
                new InputArgument(
                    'storeId',
                    InputArgument::REQUIRED,
                    'The store ID for the buttons to change'
                )
            ]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $colorHex = $input->getArgument('colorHex');
        $storeId = $input->getArgument('storeId');

        try {
            $this->validateHex($colorHex);
            $store = $this->storeManager->getStore($storeId);
            $this->addColorToStore($storeId, $store->getConfig($this::CONFIG_PATH), $colorHex);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        $output->writeln('The buttons color has been changed.');
    }

    /**
     * Check if the colorHex inputted is valid
     *
     * @param $hex
     * @throws LocalizedException
     */
    protected function validateHex($hex)
    {
        if (!ctype_xdigit($hex)) {
            throw new LocalizedException(__('Invalid hexadecimal value'));
        }
    }

    /**
     * Check if the store already has the button color
     * If not, add the cssSnippet to store design configuration
     * Then clear the cache
     *
     * @param $storeId
     * @param $storeDesign
     * @param $colorHex
     */
    protected function addColorToStore($storeId, $storeDesign, $colorHex)
    {
        $pattern = '~<style id="button-color">.actions .action{(background:[^{]*)}</style>~i';
        preg_match($pattern, $storeDesign, $match);

        if ($match) {
            $storeDesign = str_replace($match[1], 'background:#' . $colorHex, $storeDesign);
        } else {
            $storeDesign .= $this->getCssSnippet($colorHex);
        }

        $this->_writerInterface->save(
            $this::CONFIG_PATH,
            $storeDesign,
            $scope = ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $this->clearConfigCache();
    }

    /**
     * @param $colorHex
     * @return string
     */
    protected function getCssSnippet($colorHex)
    {
        return '<style id="buttons-color">.actions .action{background:#' . $colorHex . '}</style>';
    }

    /**
     * @return void
     */
    protected function clearConfigCache()
    {
        $this->_typeListInterface->cleanType('config');
        foreach ($this->_pool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
