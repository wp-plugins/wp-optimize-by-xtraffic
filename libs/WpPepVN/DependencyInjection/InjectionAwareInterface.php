<?php 
namespace WpPepVN\DependencyInjection;

/**
 * WpPepVN\DependencyInjection\InjectionAwareInterface
 *
 * This interface must be implemented in those classes that uses internally the WpPepVN\DependencyInjection that creates them
 */
interface InjectionAwareInterface
{

	/**
	 * Sets the dependency injector
	 */
	public function setDI(\WpPepVN\DependencyInjectionInterface $dependencyInjector);

	/**
	 * Returns the internal dependency injector
	 */
	public function getDI();
    
}
