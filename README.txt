# Skillshare / Browse Skillshare 
================================

### Two Mahara plugins for sharing skills and peer networking.


Skillshare allows users to post and browse 'Offered' or 'Wanted' listings in the Mahara eportfolio system. It was developed in response to a request to help students find collaborators among their peers.

Skillshare is made up of two complementary plugins - **Skillshare** and **Browse Skillshare**.
Skillshare makes the creation of listings possible, while Browse Skillshare creates a searchable directory of listings made with the Skillshare plugin.

## Requirements
---
Browse Skillshare requires Mahara 1.6 for the profile_url() function in Mahara's htdocs/lib/user.php.

Browse Skillshare requires the mod_rewrite module enabled in PHP.



## Installation
---

__Please follow the separate instructions for both plugins carefully.__

*N.B. Skillshare can be installed on its own if required, but Browse Skillshare will not function without the Skillshare plugin.*


####Skillshare installation
* Copy the 'skillshare' folder to your Mahara installation, inside the folder htdocs/artefact.
* Visit the Site Administration->Extensions->Plugin administration page and install the artefact/skillshare plugin.
* Reload the page and install the skillshare/skillshare blocktype plugin.


####Browse Skillshare installation
* Copy the 'browseskillshare' folder to your Mahara installation, inside the folder htdocs/artefact, after you have installed the Skillshare plugin.
* Visit the Site Administration->Extensions->Plugin administration page and install the artefact/browseskillshare plugin.
* Make sure mod_rewrite is enabled in your PHP installation, and add the following rule to your .htaccess file, after any existing rewrite rules:



        Rewrite Engine on
        RewriteRule ^artefact/browseskillshare/([^\.]+)/?$ artefact/browseskillshare/index.php [L]

(It's not necessary to add the 'RewriteEngine on' line if it's already there in your .htaccess file.) 

This enables the direct loading of fullscreen Skillshare listing views (pop-up views) with their own urls (e.g. http://mymahara.com/artefact/browseskillshare/listing-83482) which is useful for sharing full listings to Facebook or other social media, or by email.

**Fix redirect after messaging users in Skillshare listings**.

Edit the htdocs/user/sendmessage.php file as follows, adding the 'skillshare' switch option:

    $returnto = param_alpha('returnto', 'myfriends');
    switch ($returnto) {
        case 'find':
            $goto = 'user/find.php';
            break;
        case 'skillshare':
            $goto = 'artefact/browseskillshare';
            break;
        case 'view':
            $goto = profile_url($user, false);
            break;
        case 'inbox':
            $goto = 'account/activity';
            break;
        case 'institution':
            $goto = ($inst = param_alpha('inst', null))
            ? 'institution/index.php?institution=' . $inst
            : 'account/activity';
            break;
        default:
      $goto = 'user/myfriends.php';
    }

**Set up a menu option**.

As Skillshare consists of two separate plugins, by default it will have two menu entries in Mahara. The Skillshare menu option appears under Content. Here I describe two options for setting where the Browse Skillshare menu option appears.

**Menu option 1. Make Browse Skillshare menu appear under Dashboard.**
    
This is the default setup for the Browse Skillshare plugin, but it requires editing of some additional files.
    
In htdocs/local/lib.php, add the following code:
    
        function local_main_nav_update(&$menu){

            unset($menu['home']);

            $menu['dashboard'] = array(
            'path' => 'dashboard',
            'url' => '',
            'title' => get_string('dashboard', 'view'),
            'weight' => 10,
            'accesskey' => 'h',
            );

            $menu['dashboard/latest'] =  array(
            'path' => 'dashboard/latest',
            'url' => '',
            'title' => get_string('dashboardlatest', 'view'),
            'weight' => 10,
            );
        }
        
Next, add this line to htdocs/lang/en.utf8/view.php, or your language equivalent:
    
        $string['dashboardlatest'] = 'Latest activity';
    
Finally, edit htdocs/index.php by changing:
    
        define('MENUITEM', ''); 
        
    to:
    
        define('MENUITEM', 'dashboard');
    
**Menu option 2.    Make Browse Skillshare menu appear under Content.**
    
If you would prefer to have Browse Skillshare appear under the Content menu, edit the menu_items() function in browseskillshare/lib.php as follows:
    
            public static function menu_items() {
                return array(
                    'content/browseskillshare' => array (
                    'path' => 'content/browseskillshare',
                    'url'  => 'artefact/browseskillshare',
                    'title' => get_string('browseskillshare', 'artefact.browseskillshare'),
                    'weight' => 57,
            ),
        );
    }



## Usage
---

####Skillshare - how to use
The creation of listings is made available as an option under the 'Content' menu.
The Skillshare content block appears under the Profile content type when creating pages.

Each user has a single Skillshare listing which can be optionally published to the Skillshare directory (see Browse Skillshare below).

The display of the Skillshare block is very simple at this point. More effort has been devoted to the display of the Skillshare directory (see Browse Skillshare below).


####Browse Skillshare - how to use

The directory of listings will be available for browsing either under the Mahara Dashboard menu or the Content menu, according to your installation choice (see above).

The directory can be searched by Keyword and Sharetype (Wanted/Offered) settings.
Multiple search terms can be used at once. Current filter options are displayed and can be deleted individually or collectively.

**Additional search options:**
In the original version of the plugin, searching of content by college and course is enabled in addition to Keyword and Sharetype searches - see [Workflow - University of the Arts](http://workflow.arts.ac.uk/public/skillshare) for an example.
This functionality is extant in the code but has been commented out. Developers may wish to adapt these features to their own institutional context.
The college and course filters query an external database, and the course filter implements autocomplete when searching.


##Licence
---

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
