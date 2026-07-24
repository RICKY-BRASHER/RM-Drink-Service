import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: login.url(options),
        method: 'get',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
        loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(options),
            method: 'get',
        })
            /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
        loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    login.form = loginForm
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: register.url(options),
        method: 'get',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
        registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url(options),
            method: 'get',
        })
            /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
        registerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/gestion/personnel/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::dashboard
 * @see app/Http/Controllers/PagePersonnel.php:14
 * @route '/gestion/personnel/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
export const commandes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: commandes.url(options),
    method: 'get',
})

commandes.definition = {
    methods: ["get","head"],
    url: '/gestion/personnel/commandes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
commandes.url = (options?: RouteQueryOptions) => {
    return commandes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
commandes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: commandes.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
commandes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: commandes.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
    const commandesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: commandes.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
        commandesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: commandes.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::commandes
 * @see app/Http/Controllers/PagePersonnel.php:300
 * @route '/gestion/personnel/commandes'
 */
        commandesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: commandes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    commandes.form = commandesForm
/**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
export const vente = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: vente.url(options),
    method: 'get',
})

vente.definition = {
    methods: ["get","head"],
    url: '/gestion/ventes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
vente.url = (options?: RouteQueryOptions) => {
    return vente.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
vente.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: vente.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
vente.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: vente.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
    const venteForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: vente.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
        venteForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: vente.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::vente
 * @see app/Http/Controllers/PagePersonnel.php:263
 * @route '/gestion/ventes'
 */
        venteForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: vente.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    vente.form = venteForm
/**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
export const produit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: produit.url(options),
    method: 'get',
})

produit.definition = {
    methods: ["get","head"],
    url: '/gestion/produit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
produit.url = (options?: RouteQueryOptions) => {
    return produit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
produit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: produit.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
produit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: produit.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
    const produitForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: produit.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
        produitForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: produit.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::produit
 * @see app/Http/Controllers/PagePersonnel.php:213
 * @route '/gestion/produit'
 */
        produitForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: produit.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    produit.form = produitForm
/**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
export const controldepot = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: controldepot.url(options),
    method: 'get',
})

controldepot.definition = {
    methods: ["get","head"],
    url: '/admin/gestion/controldepot',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
controldepot.url = (options?: RouteQueryOptions) => {
    return controldepot.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
controldepot.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: controldepot.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
controldepot.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: controldepot.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
    const controldepotForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: controldepot.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
        controldepotForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: controldepot.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Pagecontrol::controldepot
 * @see app/Http/Controllers/Pagecontrol.php:145
 * @route '/admin/gestion/controldepot'
 */
        controldepotForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: controldepot.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    controldepot.form = controldepotForm
/**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
export const transaction = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: transaction.url(options),
    method: 'get',
})

transaction.definition = {
    methods: ["get","head"],
    url: '/admin/gestion/transaction',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
transaction.url = (options?: RouteQueryOptions) => {
    return transaction.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
transaction.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: transaction.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
transaction.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: transaction.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
    const transactionForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: transaction.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
        transactionForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: transaction.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Pagecontrol::transaction
 * @see app/Http/Controllers/Pagecontrol.php:155
 * @route '/admin/gestion/transaction'
 */
        transactionForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: transaction.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    transaction.form = transactionForm
/**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
export const employe = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employe.url(options),
    method: 'get',
})

employe.definition = {
    methods: ["get","head"],
    url: '/admin/gestion/employe',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
employe.url = (options?: RouteQueryOptions) => {
    return employe.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
employe.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employe.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
employe.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employe.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
    const employeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employe.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
        employeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employe.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Pagecontrol::employe
 * @see app/Http/Controllers/Pagecontrol.php:138
 * @route '/admin/gestion/employe'
 */
        employeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employe.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    employe.form = employeForm
/**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
export const client = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: client.url(options),
    method: 'get',
})

client.definition = {
    methods: ["get","head"],
    url: '/gestion/client',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
client.url = (options?: RouteQueryOptions) => {
    return client.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
client.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: client.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
client.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: client.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
    const clientForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: client.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
        clientForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: client.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::client
 * @see app/Http/Controllers/PagePersonnel.php:140
 * @route '/gestion/client'
 */
        clientForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: client.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    client.form = clientForm
/**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
export const historique = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: historique.url(options),
    method: 'get',
})

historique.definition = {
    methods: ["get","head"],
    url: '/gestion/historique',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
historique.url = (options?: RouteQueryOptions) => {
    return historique.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
historique.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: historique.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
historique.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: historique.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
    const historiqueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: historique.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
        historiqueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: historique.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::historique
 * @see app/Http/Controllers/PagePersonnel.php:153
 * @route '/gestion/historique'
 */
        historiqueForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: historique.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    historique.form = historiqueForm
/**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
export const stock = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stock.url(options),
    method: 'get',
})

stock.definition = {
    methods: ["get","head"],
    url: '/gestion/stock',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
stock.url = (options?: RouteQueryOptions) => {
    return stock.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
stock.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stock.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
stock.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stock.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
    const stockForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stock.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
        stockForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stock.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PagePersonnel::stock
 * @see app/Http/Controllers/PagePersonnel.php:233
 * @route '/gestion/stock'
 */
        stockForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stock.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    stock.form = stockForm
/**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
export const LandingPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: LandingPage.url(options),
    method: 'get',
})

LandingPage.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
LandingPage.url = (options?: RouteQueryOptions) => {
    return LandingPage.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
LandingPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: LandingPage.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
LandingPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: LandingPage.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
    const LandingPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: LandingPage.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
        LandingPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: LandingPage.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Pagecontrol::LandingPage
 * @see app/Http/Controllers/Pagecontrol.php:12
 * @route '/'
 */
        LandingPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: LandingPage.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    LandingPage.form = LandingPageForm
/**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
export const deconnect = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deconnect.url(options),
    method: 'get',
})

deconnect.definition = {
    methods: ["get","head"],
    url: '/deconnect',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
deconnect.url = (options?: RouteQueryOptions) => {
    return deconnect.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
deconnect.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deconnect.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
deconnect.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: deconnect.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
    const deconnectForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: deconnect.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
        deconnectForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deconnect.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Authentification::deconnect
 * @see app/Http/Controllers/Authentification.php:218
 * @route '/deconnect'
 */
        deconnectForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deconnect.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    deconnect.form = deconnectForm