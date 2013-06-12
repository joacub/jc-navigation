<?php
namespace JcNavigation\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="navigation")
 * use repository for handy tree functions
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class Navigation
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=64)
     */
    protected $title;
    
    /**
     * @ORM\Column(name="titleAttribute", type="string", length=64, nullable=true)
     */
    protected $titleAttribute;
    
    /**
     * @ORM\Column(name="target", type="boolean")
     */
    protected $target = false;
    
    /**
     * @ORM\Column(name="css", type="string", nullable=true)
     */
    protected $css;
    
    /**
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Navigation", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="Navigation", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;
    
    /**
     * 
     * @ORM\Column(name="collector", type="string", nullable=true)
     */
    protected $collector;
    
    /**
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $referenceId;
    
    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return the $titleAttribute
     */
    public function getTitleAttribute ()
    {
    	return $this->titleAttribute;
    }
    
    /**
     * @param field_type $titleAttribute
     */
    public function setTitleAttribute ($titleAttribute)
    {
    	$this->titleAttribute = $titleAttribute;
    }
    
    /**
     * @return the $target
     */
    public function getTarget ()
    {
    	return $this->target;
    }
    
    /**
     * @param field_type $target
     */
    public function setTarget ($target)
    {
    	$this->target = $target;
    }
    
    /**
     * @return the $css
     */
    public function getCss ()
    {
    	return $this->css;
    }
    
    /**
     * @param field_type $css
     */
    public function setCss ($css)
    {
    	$this->css = $css;
    }
    
    /**
     * @return the $description
     */
    public function getDescription ()
    {
    	return $this->description;
    }
    
    /**
     * @param field_type $description
     */
    public function setDescription ($description)
    {
    	$this->description = $description;
    }
    
    public function getLevel()
    {
    	return $this->lvl;
    }

    public function setParent(Navigation $parent = null)
    {
        $this->parent = $parent;    
    }

    public function getParent()
    {
        return $this->parent;   
    }
    
    public function setCollector($collector)
    {
    	$this->collector = $collector;
    }
    
    public function getCollector()
    {
    	return $this->collector;
    }
	/**
	 * @return the $referenceId
	 */
	public function getReferenceId ()
	{
		return $this->referenceId;
	}

	/**
	 * @param field_type $referenceId
	 */
	public function setReferenceId ($referenceId)
	{
		$this->referenceId = $referenceId;
	}


}
