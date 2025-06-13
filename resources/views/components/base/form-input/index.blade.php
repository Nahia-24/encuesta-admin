@props(['formInputSize' => null, 'rounded' => null])

<input
    data-tw-merge
    {{ $attributes->class([
            'disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-slate-800 dark:disabled:border-slate-800',
            '[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-slate-800',
            'transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-slate-800 dark:border-slate-700 dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80',
            'group-[.form-inline]:flex-1',
            'group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child):not(:last-child)]:rounded-none group-[.input-group]:first:rounded-l-md group-[.input-group]:last:rounded-r-md',
            $formInputSize == 'sm' ? 'text-xs py-1.5 px-2' : null,
            $formInputSize == 'lg' ? 'text-lg py-1.5 px-4' : null,
            $rounded ? 'rounded-full' : null,
            $attributes->whereStartsWith('class')->first(),

    ])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
            
            

            
            
                
                
                
                
           
        
/>
